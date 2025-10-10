<?php

namespace App\Console\Commands;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;

class TestAttendeeById extends Command
{
    protected $signature = 'test:attendee-by-id {attendee-id}';
    protected $description = 'Test getting attendee by ID';

    public function handle(UnipileService $unipileService): int
    {
        $this->info('🔍 Testing Attendee by ID...');
        
        $account = IntegratedAccount::where('provider', 'whatsapp')
            ->where('status', 'active')
            ->first();
        
        if (!$account) {
            $this->error('❌ No active WhatsApp account found');
            return 1;
        }
        
        $attendeeId = $this->argument('attendee-id');
        
        $this->info("📱 Account: {$account->unipile_account_id}");
        $this->info("👤 Attendee ID: {$attendeeId}");
        $this->newLine();
        
        // Try GET /attendees/{id}
        $this->info('📋 Testing GET /attendees/{id}...');
        
        try {
            $attendee = $unipileService->getAttendee($attendeeId, $account->unipile_account_id);
            
            if (!empty($attendee)) {
                $this->info('✅ Attendee found!');
                $this->newLine();
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['id', $attendee['id'] ?? 'NULL'],
                        ['provider_id', $attendee['provider_id'] ?? 'NULL'],
                        ['display_name', $attendee['display_name'] ?? '🚫 NULL'],
                        ['identifier', $attendee['identifier'] ?? 'NULL'],
                        ['type', $attendee['type'] ?? 'NULL'],
                        ['is_me', isset($attendee['is_me']) ? ($attendee['is_me'] ? 'true' : 'false') : 'NULL'],
                    ]
                );
                $this->newLine();
                $this->line('Full attendee data:');
                $this->line(json_encode($attendee, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->error("❌ Attendee not found or empty response");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
        
        return 0;
    }
}

