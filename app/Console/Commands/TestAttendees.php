<?php

namespace App\Console\Commands;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;

class TestAttendees extends Command
{
    protected $signature = 'test:attendees {phone?}';
    protected $description = 'Test Unipile attendees and profile API';

    public function handle(UnipileService $unipileService): int
    {
        $this->info('🔍 Testing Unipile Attendees API...');
        
        $account = IntegratedAccount::where('provider', 'whatsapp')
            ->where('status', 'active')
            ->first();
        
        if (!$account) {
            $this->error('❌ No active WhatsApp account found');
            return 1;
        }
        
        $this->info("📱 Account: {$account->unipile_account_id}");
        $this->newLine();
        
        // Test 1: List Attendees
        $this->info('📋 Testing listAttendees()...');
        $attendees = $unipileService->listAttendees($account->unipile_account_id);
        
        $totalAttendees = count($attendees['items'] ?? []);
        $this->info("✅ Retrieved {$totalAttendees} attendees");
        
        if ($totalAttendees > 0) {
            $this->newLine();
            $this->info('📄 First 5 attendees:');
            
            foreach (array_slice($attendees['items'] ?? [], 0, 5) as $index => $attendee) {
                $this->newLine();
                $this->line("━━━━━━━━━━━━━━━━━━━━━━━ Attendee #{$index} ━━━━━━━━━━━━━━━━━━━━━━━");
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['provider_id', $attendee['provider_id'] ?? 'NULL'],
                        ['display_name', $attendee['display_name'] ?? '🚫 NULL'],
                        ['identifier', $attendee['identifier'] ?? 'NULL'],
                        ['type', $attendee['type'] ?? 'NULL'],
                        ['is_me', isset($attendee['is_me']) ? ($attendee['is_me'] ? 'true' : 'false') : 'NULL'],
                    ]
                );
                $this->line('Full data: ' . json_encode($attendee, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }
        
        // Test 2: Get specific profile
        $searchPhone = $this->argument('phone');
        if ($searchPhone) {
            $this->newLine(2);
            $this->info("🔎 Searching for phone: {$searchPhone}");
            
            $phoneDigits = preg_replace('/[^0-9]/', '', $searchPhone);
            $providerId = $phoneDigits . '@s.whatsapp.net';
            
            $this->info("📞 Provider ID: {$providerId}");
            $this->newLine();
            
            $this->info('📋 Testing getProfile()...');
            $profile = $unipileService->getProfile($account->unipile_account_id, $providerId);
            
            if (!empty($profile)) {
                $this->info('✅ Profile found!');
                $this->newLine();
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['provider_id', $profile['provider_id'] ?? 'NULL'],
                        ['display_name', $profile['display_name'] ?? '🚫 NULL'],
                        ['identifier', $profile['identifier'] ?? 'NULL'],
                        ['name', $profile['name'] ?? 'NULL'],
                        ['type', $profile['type'] ?? 'NULL'],
                        ['is_me', isset($profile['is_me']) ? ($profile['is_me'] ? 'true' : 'false') : 'NULL'],
                    ]
                );
                $this->newLine();
                $this->line('Full profile data:');
                $this->line(json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->error('❌ Profile not found or empty response');
            }
        }
        
        return 0;
    }
}

