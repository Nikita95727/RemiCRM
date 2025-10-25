<?php

namespace App\Console\Commands;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;

class TestWhatsAppMessages extends Command
{
    protected $signature = 'test:wa-messages {chat-id}';
    protected $description = 'Test WhatsApp messages to find contact name';

    public function handle(UnipileService $unipileService): int
    {
        $this->info('🔍 Testing WhatsApp Messages API...');
        
        $account = IntegratedAccount::where('provider', 'whatsapp')
            ->where('status', 'active')
            ->first();
        
        if (!$account) {
            $this->error('❌ No active WhatsApp account found');
            return 1;
        }
        
        $chatId = $this->argument('chat-id');
        
        $this->info("📱 Account: {$account->unipile_account_id}");
        $this->info("💬 Chat ID: {$chatId}");
        $this->newLine();
        
        $this->info('📥 Fetching messages...');
        $messagesData = $unipileService->listChatMessages(
            $account->unipile_account_id,
            $chatId,
            10  // Just get 10 messages for testing
        );
        
        $messages = $messagesData['messages'] ?? [];
        $totalMessages = count($messages);
        
        $this->info("✅ Retrieved {$totalMessages} messages");
        
        if ($totalMessages > 0) {
            $this->newLine();
            $this->info('📄 First 3 messages (to find sender name):');
            
            foreach (array_slice($messages, 0, 3) as $index => $message) {
                $this->newLine();
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━ Message #{$index} ━━━━━━━━━━━━━━━━━━━━━━━");
                
                // Extract all name-related fields
                $nameFields = [
                    'from.display_name' => $message['from']['display_name'] ?? null,
                    'from.identifier' => $message['from']['identifier'] ?? null,
                    'from.provider_id' => $message['from']['provider_id'] ?? null,
                    'from.name' => $message['from']['name'] ?? null,
                    'author.display_name' => $message['author']['display_name'] ?? null,
                    'author.identifier' => $message['author']['identifier'] ?? null,
                    'author.name' => $message['author']['name'] ?? null,
                    'sender_name' => $message['sender_name'] ?? null,
                    'contact_name' => $message['contact_name'] ?? null,
                ];
                
                $this->table(
                    ['Field', 'Value'],
                    array_map(fn($k, $v) => [$k, $v ?? '🚫 NULL'], array_keys($nameFields), $nameFields)
                );
                
                $this->newLine();
                $this->line('All available message fields:');
                $this->line(json_encode(array_keys($message), JSON_PRETTY_PRINT));
                
                $this->newLine();
                $this->line('Full message data:');
                $this->line(json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        } else {
            $this->warn('⚠️  No messages found in this chat');
        }
        
        return 0;
    }
}


