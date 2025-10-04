<?php

declare(strict_types=1);

namespace App\Modules\Integration\Jobs;

use App\Models\ImportStatus;
use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\ChatAnalysisService;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BatchAutoTagContacts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes for single contact processing
    public int $tries = 2;

    public function __construct(
        private IntegratedAccount $account,
        private int $batchSize = 1 // Process 1 contact at a time for memory efficiency
    ) {}

    public function handle(UnipileService $unipileService, ChatAnalysisService $chatAnalysisService, ContactRepositoryInterface $contactRepository): void
    {
        try {
            Log::info('🏷️ BATCH TAGGING - Starting mass tagging process', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider->value,
                'batch_size' => $this->batchSize,
            ]);

            // Mark as tagging in progress
            ImportStatus::setTagging($this->account->user_id, $this->account->provider->value);

            // Get untagged contacts for this account using repository
            $untaggedContacts = $contactRepository->findUntaggedByAccount(
                $this->account->id,
                $this->account->user_id
            );

            if ($untaggedContacts->isEmpty()) {
                Log::info('🏷️ BATCH TAGGING - No untagged contacts found', [
                    'account_id' => $this->account->id,
                ]);
                return;
            }

            Log::info('🏷️ BATCH TAGGING - Found untagged contacts', [
                'account_id' => $this->account->id,
                'total_contacts' => $untaggedContacts->count(),
            ]);

            // Process contacts in batches to avoid memory issues
            $this->processContactsInBatches($untaggedContacts, $unipileService, $chatAnalysisService);

            // Mark tagging as completed
            ImportStatus::completeImport($this->account->user_id, $this->account->provider->value);

            Log::info('🎉 BATCH TAGGING - Mass tagging completed', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider->value,
            ]);

        } catch (\Exception $e) {
            Log::error('❌ BATCH TAGGING - Error during mass tagging', [
                'account_id' => $this->account->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }


    /**
     * Process contacts in small batches to minimize server load
     */
    private function processContactsInBatches($contacts, UnipileService $unipileService, ChatAnalysisService $chatAnalysisService): void
    {
        $totalContacts = $contacts->count();
        $totalProcessed = 0;
        $totalTagged = 0;
        $batchNumber = 0;

        foreach ($contacts->chunk($this->batchSize) as $batch) {
            $batchNumber++;
            $batchTagged = 0;

            foreach ($batch as $contact) {
                try {
                    $tagged = $this->tagSingleContact($contact, $unipileService, $chatAnalysisService);
                    if ($tagged) {
                        $batchTagged++;
                        $totalTagged++;
                    }
                    $totalProcessed++;

                } catch (\Exception $e) {
                    Log::warning('⚠️ Failed to tag contact in batch', [
                        'contact_id' => $contact->id,
                        'contact_name' => $contact->name,
                        'error' => $e->getMessage(),
                    ]);
                    $totalProcessed++;
                }
            }

            // Memory cleanup after each contact for low-memory servers
            unset($batch);
            gc_collect_cycles();

            // Small delay between contacts to reduce server load
            usleep(100000); // 100ms pause between each contact
        }

        Log::info('📊 BATCH TAGGING - Final statistics', [
            'account_id' => $this->account->id,
            'total_contacts' => $totalContacts,
            'total_processed' => $totalProcessed,
            'total_tagged' => $totalTagged,
            'success_rate' => $totalContacts > 0 ? round(($totalTagged / $totalContacts) * 100, 2) . '%' : '0%',
        ]);
    }

    /**
     * Tag a single contact
     */
    private function tagSingleContact(Contact $contact, UnipileService $unipileService, ChatAnalysisService $chatAnalysisService): bool
    {
        try {
            $integration = $contact->integrations->first();
            
            if (!$integration) {
                return false;
            }

            $chatId = $integration->external_id;
            $accountId = $this->account->unipile_account_id;

            // Try to get messages from the chat/email using optimized method
            $messages = match ($this->account->provider->value) {
                'telegram', 'whatsapp' => $unipileService->getMessagesForAnalysis($accountId, $chatId, 100), // Memory-efficient with cursor pagination
                'google_oauth' => [], // Gmail doesn't have chat messages in the same format
                default => [],
            };

            // For Gmail or if no messages found, try fallback tagging by name
            if (empty($messages['messages']) && $this->account->provider->value !== 'google_oauth') {
                // Try to tag based on contact name or username as fallback
                $tag = $this->generateFallbackTag($contact);
                if ($tag) {
                    $contact->update(['tags' => [$tag]]);
                    return true;
                }
                
                return false;
            }

            // For Gmail, we can tag based on email content or domain
            if ($this->account->provider->value === 'google_oauth') {
                $tag = $this->generateGmailTag($contact);
            } else {
                // Analyze chat messages for Telegram/WhatsApp
                $tag = $chatAnalysisService->analyzeChatMessages($messages['messages']);
            }

            if ($tag) {
                $contact->update(['tags' => [$tag]]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::warning('Failed to tag individual contact', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate tag for Gmail contacts based on email domain or content
     */
    private function generateGmailTag(Contact $contact): ?string
    {
        if (!$contact->email) {
            return null;
        }

        $domain = substr(strrchr($contact->email, "@"), 1);
        
        // Common business domains
        $businessDomains = [
            'gmail.com' => 'personal',
            'yahoo.com' => 'personal',
            'hotmail.com' => 'personal',
            'outlook.com' => 'personal',
            'company.com' => 'business',
            'corp.com' => 'business',
        ];

        // Check for known business indicators in domain
        if (str_contains($domain, 'bank')) return 'banking';
        if (str_contains($domain, 'crypto') || str_contains($domain, 'coin')) return 'crypto';
        if (str_contains($domain, 'ad') || str_contains($domain, 'marketing')) return 'advertising';
        if (str_contains($domain, 'tech') || str_contains($domain, 'dev')) return 'technology';

        // Check contact name for business indicators
        $name = strtolower($contact->name);
        if (str_contains($name, 'bank') || str_contains($name, 'financial')) return 'banking';
        if (str_contains($name, 'crypto') || str_contains($name, 'bitcoin')) return 'crypto';
        if (str_contains($name, 'marketing') || str_contains($name, 'ads')) return 'advertising';
        if (str_contains($name, 'team') || str_contains($name, 'support')) return 'business';

        // Default based on domain type
        return $businessDomains[$domain] ?? 'business';
    }

    /**
     * Generate fallback tag based on contact name when no messages available
     * Uses keyword matching on contact name/username
     */
    private function generateFallbackTag(Contact $contact): ?string
    {
        $name = mb_strtolower($contact->name, 'UTF-8');
        
        // Banking keywords
        if (preg_match('/(bank|mono|privat|pumb|raiff|alpha|finance|wallet|pay)/ui', $name)) {
            return 'banking';
        }
        
        // Crypto keywords
        if (preg_match('/(crypto|bitcoin|btc|eth|coin|token|blockchain|ton|pocket|hot|cold)/ui', $name)) {
            return 'crypto';
        }
        
        // Gaming keywords
        if (preg_match('/(game|gaming|poker|casino|play|pixel|hamster|tap|clicker)/ui', $name)) {
            return 'gaming';
        }
        
        // Business keywords
        if (preg_match('/(llc|ltd|inc|corp|company|group|team|support|service|official)/ui', $name)) {
            return 'business';
        }
        
        // Bot keywords
        if (preg_match('/(bot|_bot|assistant|helper|notify)/ui', $name)) {
            return 'bot';
        }
        
        // Technology keywords
        if (preg_match('/(dev|tech|code|api|software|app|digital)/ui', $name)) {
            return 'technology';
        }
        
        // Advertising keywords
        if (preg_match('/(ad|ads|promo|marketing|campaign|advertising)/ui', $name)) {
            return 'advertising';
        }
        
        // If contains @username format or ends with common messenger patterns, likely personal
        if (preg_match('/^@|^[a-z0-9_]+$/ui', $name) && !str_contains($name, ' ')) {
            return 'social';
        }
        
        // If has emoji or special characters, likely personal
        if (preg_match('/[\x{1F300}-\x{1F9FF}]|❤️|♣️|♠️|♥️|♦️/u', $name)) {
            return 'social';
        }
        
        // Default: no tag (better than incorrect tag)
        return null;
    }
}
