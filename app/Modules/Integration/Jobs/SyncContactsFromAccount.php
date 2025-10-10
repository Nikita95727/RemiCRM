<?php

declare(strict_types=1);

namespace App\Modules\Integration\Jobs;

use App\Models\ImportStatus;
use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Contact\DTOs\CreateContactDTO;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use App\Modules\Integration\Transformers\ContactTransformerFactory;
use App\Modules\Integration\Jobs\BatchAutoTagContacts;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SyncContactsFromAccount implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes
    public int $tries = 3;

    public function __construct(
        private IntegratedAccount $account
    ) {}

    public function handle(UnipileService $unipileService, ContactRepositoryInterface $contactRepository): void
    {
        try {
            if (! $this->account->isActive()) {
                Log::info('Skipping sync for inactive account', ['account_id' => $this->account->id]);
                return;
            }

            Log::info('🚀 OPTIMIZED STREAMING SYNC - Memory-safe processing', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider,
                'job_class' => self::class,
            ]);

            // Start import tracking
            ImportStatus::startImport($this->account->user_id, $this->account->provider->value);

            // Use streaming approach to avoid memory issues
            $this->processContactsInBatches($unipileService, $contactRepository);

            $this->account->update(['last_sync_at' => now()]);

            // Mark import as completed and start tagging
            ImportStatus::completeImport($this->account->user_id, $this->account->provider->value);
            
            // Start batch tagging immediately after sync completion
            // BatchAutoTagContacts::dispatch($this->account); // OLD: Queued version
            
            // NEW: IMMEDIATE batch tagging for testing
            Log::info('ContactSyncService: Running IMMEDIATE batch tagging (not queued)', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider,
            ]);
            
            $batchTaggingJob = new BatchAutoTagContacts($this->account);
            $batchTaggingJob->handle(
                app(\App\Modules\Integration\Services\UnipileService::class),
                app(\App\Modules\Contact\Contracts\ContactRepositoryInterface::class)
            );
            
            Log::info('ContactSyncService: IMMEDIATE batch tagging completed', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider,
            ]);

            Log::info('🎉 OPTIMIZED SYNC - Streaming contact sync completed', [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider,
                'batch_tagging_scheduled' => true,
            ]);

        } catch (\Exception $e) {
            // Mark import as failed
            ImportStatus::failImport($this->account->user_id, $this->account->provider->value, $e->getMessage());
            
            Log::error('OPTIMIZED SYNC - Contact sync error: '.$e->getMessage(), [
                'account_id' => $this->account->id,
                'provider' => $this->account->provider,
                'exception' => $e,
            ]);

            $this->account->update(['status' => 'error']);
            throw $e;
        }
    }

    /**
     * Process contacts in batches using streaming approach to avoid memory issues
     */
    private function processContactsInBatches(UnipileService $unipileService, ContactRepositoryInterface $contactRepository): void
    {
        $transformerFactory = new ContactTransformerFactory();
        $transformer = $transformerFactory->create($this->account->provider->value);
        
        $totalProcessed = 0;
        $totalSaved = 0;
        $batchSize = 25; // Smaller batches for memory efficiency
        
        // Define callback for processing each batch
        $processBatch = function (array $items, int $page, ?string $cursor) use ($transformer, &$totalProcessed, &$totalSaved, $batchSize, $contactRepository): bool {
            try {
                // Transform batch to contact DTOs
                $rawData = ['items' => $items];
                $contactDTOs = $transformer->transform($rawData, $this->account->user_id);
                
                // Save contacts to database
                $savedContacts = $this->saveContacts($contactDTOs, $contactRepository);
                
                $totalProcessed += count($items);
                $totalSaved += count($savedContacts);

                // Update import progress
                ImportStatus::updateProgress(
                    $this->account->user_id, 
                    $this->account->provider->value, 
                    $totalSaved,
                    "Imported {$totalSaved} contacts..."
                );

                Log::info("Batch {$page} completed", [
                    'processed' => count($items),
                    'transformed' => count($contactDTOs),
                    'saved' => count($savedContacts),
                    'total_processed' => $totalProcessed,
                    'total_saved' => $totalSaved,
                ]);

                // Force garbage collection after each batch
                gc_collect_cycles();
                
                return true; // Continue processing

            } catch (\Exception $e) {
                Log::error("Batch {$page} failed", [
                    'error' => $e->getMessage(),
                    'items_count' => count($items),
                ]);
                
                return true; // Continue with next batch even if this one fails
            }
        };

        // Stream data based on provider type using manual pagination
        $result = $this->streamDataManually($unipileService, $processBatch, $batchSize);

        Log::info('Streaming sync completed', [
            'provider' => $this->account->provider->value,
            'pages_processed' => $result['pages_processed'],
            'api_total_items' => $result['total_items'],
            'contacts_processed' => $totalProcessed,
            'contacts_saved' => $totalSaved,
            'errors' => count($result['errors']),
            'completed' => $result['completed'],
        ]);

        if (!empty($result['errors'])) {
            Log::warning('Some batches had errors during sync', [
                'errors' => $result['errors'],
            ]);
        }
    }


    /**
     * @param  CreateContactDTO[]  $contactDTOs
     * @return Contact[]
     */
    private function saveContacts(array $contactDTOs, ContactRepositoryInterface $contactRepository): array
    {
        $savedContacts = [];
        $user = $this->account->user;

        foreach ($contactDTOs as $contactDTO) {
            try {
                // Check if contact already exists using repository
                $existingContact = null;

                // First, try to find by provider_id (most accurate for external contacts)
                if ($contactDTO->providerId) {
                    $existingContact = $contactRepository->findByProviderId($user, $contactDTO->providerId);
                }

                // If not found, try email/phone
                if (!$existingContact && ($contactDTO->email || $contactDTO->phone)) {
                    $existingContact = $contactRepository->findByEmailOrPhone(
                        $user,
                        $contactDTO->email,
                        $contactDTO->phone
                    );
                }

                // Last fallback: name search (only if no provider_id, email, or phone)
                if (!$existingContact && !$contactDTO->providerId && !$contactDTO->email && !$contactDTO->phone) {
                    $existingContact = $contactRepository->findByName($user, $contactDTO->name);
                }

                if ($existingContact) {
                    // Update existing contact with new sources
                    $existingSources = $existingContact->sources ?? [];
                    $newSources = array_unique(array_merge($existingSources, $contactDTO->sources));
                    
                    $contactRepository->update($existingContact, [
                        'sources' => $newSources,
                        'notes' => $contactDTO->notes ?: $existingContact->notes,
                    ]);

                    $savedContacts[] = $existingContact;
                } else {
                    // Create new contact using repository
                    $contact = $contactRepository->create($contactDTO->toArray());
                    $savedContacts[] = $contact;
                }

                // Create or update integration record with chat_id for message fetching
                $contact = $savedContacts[array_key_last($savedContacts)];
                $externalId = $contactDTO->chatId ?? $contactDTO->providerId ?? 'unknown';
                
                // Check if integration already exists
                $existingIntegration = $contact->integrations()
                    ->where('integrated_account_id', $this->account->id)
                    ->first();
                
                if (!$existingIntegration) {
                    Log::debug('Creating new integration', [
                        'contact_name' => $contact->name,
                        'external_id' => $externalId,
                    ]);
                    
                    $contact->integrations()->create([
                        'integrated_account_id' => $this->account->id,
                        'external_id' => $externalId,
                    ]);
                } else {
                    // Update external_id if changed
                    if ($existingIntegration->external_id !== $externalId) {
                        $existingIntegration->update(['external_id' => $externalId]);
                    }
                }

            } catch (\Exception $e) {
                Log::error('Failed to save contact', [
                    'contact_data' => $contactDTO->toArray(),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $savedContacts;
    }

    /**
     * Manual streaming implementation using existing methods
     */
    private function streamDataManually(UnipileService $unipileService, callable $processBatch, int $batchSize): array
    {
        $cursor = null;
        $currentPage = 0;
        $totalProcessed = 0;
        $errors = [];
        $maxPages = 20;

        do {
            $currentPage++;
            
            try {
                // Use existing methods based on provider
                $response = match ($this->account->provider->value) {
                    'telegram', 'whatsapp' => $unipileService->listChats($this->account->unipile_account_id, $batchSize, $cursor),
                    'google_oauth' => $unipileService->listEmails($this->account->unipile_account_id, $batchSize, $cursor),
                    default => ['items' => []],
                };

                if (empty($response['items'])) {
                    break;
                }

                // Process batch with callback
                $shouldContinue = $processBatch($response['items'], $currentPage, $cursor);
                
                $totalProcessed += count($response['items']);
                $cursor = $response['cursor'] ?? null;

                // Allow callback to stop processing
                if ($shouldContinue === false) {
                    break;
                }

                // Memory cleanup
                unset($response);
                
                // Small delay to prevent API rate limiting
                if ($currentPage % 5 === 0) {
                    usleep(100000); // 100ms pause every 5 pages
                }

            } catch (\Exception $e) {
                $errors[] = [
                    'page' => $currentPage,
                    'error' => $e->getMessage(),
                    'cursor' => $cursor
                ];
                
                Log::error("Manual streaming page {$currentPage} failed", [
                    'error' => $e->getMessage(),
                    'provider' => $this->account->provider->value,
                ]);
                
                // Continue with next page on error
                $cursor = null;
            }

        } while ($cursor && $currentPage < $maxPages);

        return [
            'pages_processed' => $currentPage,
            'total_items' => $totalProcessed,
            'errors' => $errors,
            'completed' => empty($cursor) || $currentPage >= $maxPages
        ];
    }
}
