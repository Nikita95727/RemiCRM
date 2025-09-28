<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Contracts\ContactSyncServiceInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAllContacts extends Command
{
    protected $signature = 'contacts:sync-all
                            {--user= : Sync contacts for specific user ID only}
                            {--provider= : Sync contacts for specific provider only (telegram, whatsapp, gmail)}
                            {--force : Force sync even if recently synced}';

    protected $description = 'Automatically sync contacts from all integrated accounts for all users';

    public function __construct(
        private ContactSyncServiceInterface $contactSyncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔄 Starting automatic contact synchronization...');

        try {
            $userFilter = $this->option('user');
            $providerFilter = $this->option('provider');
            $force = $this->option('force');

            // Get users to sync
            $users = $userFilter
                ? User::where('id', $userFilter)->get()
                : User::all();

            if ($users->isEmpty()) {
                $this->error('❌ No users found to sync');
                return 1;
            }

            $totalSynced = 0;
            $totalSkipped = 0;

            foreach ($users as $user) {
                $accountsQuery = IntegratedAccount::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('sync_enabled', true);

                if ($providerFilter) {
                    $accountsQuery->where('provider', strtolower($providerFilter));
                }

                if (!$force) {
                    $accountsQuery->where(function ($query) {
                        $query->whereNull('last_sync_at')
                            ->orWhere('last_sync_at', '<', now()->subHours(12));
                    });
                }

                $accounts = $accountsQuery->get();

                if ($accounts->isEmpty()) {
                    continue;
                }

                foreach ($accounts as $account) {
                    try {
                        $this->contactSyncService->queueContactSync($account);
                        $account->update(['last_sync_at' => now()]);

                        $totalSynced++;
                        $this->line("   ✅ Queued sync for {$account->provider->value} account");

                    } catch (\Exception $e) {
                        $this->error("   ❌ Failed to sync {$account->provider->value} account: " . $e->getMessage());
                        Log::error('Contact sync failed for account', [
                            'account_id' => $account->id,
                            'user_id' => $user->id,
                            'provider' => $account->provider,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $this->info('🎉 Synchronization completed!');
            $this->info("✅ Synced: {$totalSynced} accounts");
            $this->info("⏭️  Skipped: {$totalSkipped} accounts");

            if ($totalSynced > 0) {
                $this->info('💡 Contact sync jobs have been queued. Run queue worker to process them.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Synchronization failed: ' . $e->getMessage());
            Log::error('Automatic contact sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }
    }
}
