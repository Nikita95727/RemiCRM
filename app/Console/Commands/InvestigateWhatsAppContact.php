<?php

namespace App\Console\Commands;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class InvestigateWhatsAppContact extends Command
{
    protected $signature = 'whatsapp:investigate 
                            {phone? : Phone number to search for (e.g., +393926197956)}
                            {--account-id= : Specific account ID to search in}';

    protected $description = 'Investigate WhatsApp contact data structure from Unipile API';

    public function handle(UnipileService $unipileService): int
    {
        $this->info('🔍 Starting WhatsApp contact investigation...');
        
        // Get WhatsApp account
        $accountQuery = IntegratedAccount::where('provider', 'whatsapp')
            ->where('status', 'active');
        
        if ($accountId = $this->option('account-id')) {
            $accountQuery->where('id', $accountId);
        }
        
        $account = $accountQuery->first();
        
        if (!$account) {
            $this->error('❌ No active WhatsApp account found');
            return 1;
        }
        
        $this->info("📱 Found WhatsApp account: {$account->unipile_account_id}");
        $this->info("👤 User ID: {$account->user_id}");
        $this->newLine();
        
        // Get all chats
        $this->info('📥 Fetching chats from Unipile API...');
        $chatsData = $unipileService->listAllChats($account->unipile_account_id);
        
        $totalChats = count($chatsData['items'] ?? []);
        $this->info("✅ Retrieved {$totalChats} chats");
        $this->newLine();
        
        // Search for specific phone if provided
        $searchPhone = $this->argument('phone');
        
        if ($searchPhone) {
            $this->info("🔎 Searching for phone number: {$searchPhone}");
            $phoneDigits = preg_replace('/[^0-9]/', '', $searchPhone);
            $this->info("📞 Normalized phone: {$phoneDigits}");
            $this->newLine();
        }
        
        // Analyze chats
        $privateChats = 0;
        $groupChats = 0;
        $chatsWithNames = 0;
        $chatsWithoutNames = 0;
        $foundTargetContact = false;
        
        foreach ($chatsData['items'] ?? [] as $index => $chat) {
            $chatType = $chat['type'] ?? null;
            $chatName = $chat['name'] ?? null;
            $providerId = $chat['attendee_provider_id'] ?? null;
            $chatId = $chat['id'] ?? null;
            
            // Count chat types
            if ($chatType === 0) {
                $privateChats++;
            } else {
                $groupChats++;
            }
            
            // Count name presence
            if ($chatName) {
                $chatsWithNames++;
            } else {
                $chatsWithoutNames++;
            }
            
            // Check if this is the target contact
            $isTarget = false;
            if ($searchPhone && $providerId) {
                $providerDigits = preg_replace('/[^0-9]/', '', $providerId);
                if (str_contains($providerDigits, $phoneDigits)) {
                    $isTarget = true;
                    $foundTargetContact = true;
                }
            }
            
            // Display first 10 chats or target contact
            if ($index < 10 || $isTarget) {
                $this->newLine();
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                $this->line($isTarget ? "🎯 TARGET CONTACT FOUND!" : "Chat #{$index}");
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['chat_id', $chatId ?? 'NULL'],
                        ['type', $chatType . ' (' . ($chatType === 0 ? 'private' : 'group/other') . ')'],
                        ['name', $chatName ?? '🚫 NULL (NO NAME)'],
                        ['attendee_provider_id', $providerId ?? 'NULL'],
                        ['timestamp', $chat['timestamp'] ?? 'NULL'],
                        ['unread_count', $chat['unread_count'] ?? 0],
                        ['archived', isset($chat['archived']) ? ($chat['archived'] ? 'true' : 'false') : 'NULL'],
                        ['pinned', isset($chat['pinned']) ? ($chat['pinned'] ? 'true' : 'false') : 'NULL'],
                    ]
                );
                
                // Show all available fields
                $this->info('📋 All available fields in chat object:');
                $this->line(json_encode(array_keys($chat), JSON_PRETTY_PRINT));
                
                // Show full chat data
                $this->info('📄 Full chat data:');
                $this->line(json_encode($chat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
        
        // Summary
        $this->newLine(2);
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info('📊 SUMMARY STATISTICS');
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total chats', $totalChats],
                ['Private chats (type=0)', $privateChats],
                ['Group/Other chats', $groupChats],
                ['Chats WITH name field', $chatsWithNames],
                ['Chats WITHOUT name field', $chatsWithoutNames],
                ['Name field presence rate', $totalChats > 0 ? round(($chatsWithNames / $totalChats) * 100, 2) . '%' : '0%'],
            ]
        );
        
        if ($searchPhone) {
            $this->newLine();
            if ($foundTargetContact) {
                $this->info("✅ Target contact {$searchPhone} was found and displayed above");
            } else {
                $this->error("❌ Target contact {$searchPhone} NOT FOUND in {$totalChats} chats");
                $this->warn('💡 Tip: The phone number might be in a different format or not synced yet');
            }
        }
        
        // Log to file for detailed analysis
        $logData = [
            'timestamp' => now()->toDateTimeString(),
            'account_id' => $account->unipile_account_id,
            'total_chats' => $totalChats,
            'private_chats' => $privateChats,
            'chats_with_names' => $chatsWithNames,
            'chats_without_names' => $chatsWithoutNames,
            'first_10_chats' => array_slice($chatsData['items'] ?? [], 0, 10),
        ];
        
        Log::channel('single')->info('WhatsApp Contact Investigation', $logData);
        
        $this->newLine();
        $this->info('✅ Investigation complete! Full data logged to storage/logs/laravel.log');
        
        return 0;
    }
}

