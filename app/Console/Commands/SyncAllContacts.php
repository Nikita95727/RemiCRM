<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Contracts\ContactSyncServiceInterface;
use App\Modules\Integration\Jobs\SyncContactsFromAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncAllContacts extends Command
{
    protected $signature = 'contacts:sync-all
                            {--user= : Sync contacts for specific user ID only}
                            {--provider= : Sync contacts for specific provider only (telegram, whatsapp, gmail)}
                            {--force : Force sync even if recently synced}
                            {--sync : Run synchronously without queue (blocks until complete)}';

    protected $description = 'Automatically sync contacts from all integrated accounts for all users';

    public function __construct(
        private ContactSyncServiceInterface $contactSyncService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $startTime = now();
        $this->info('🔄 Starting automatic contact synchronization...');
        
        // Detailed start log
        Log::info('============================================');
        Log::info('📅 SCHEDULED SYNC STARTED', [
            'timestamp' => $startTime->toDateTimeString(),
            'command' => 'contacts:sync-all',
            'options' => [
                'user' => $this->option('user'),
                'provider' => $this->option('provider'),
                'force' => $this->option('force'),
                'sync' => $this->option('sync'),
            ],
        ]);

        try {
            $userFilter = $this->option('user');
            $providerFilter = $this->option('provider');
            $force = $this->option('force');
            $sync = $this->option('sync');

            if ($sync) {
                $this->warn('⚠️  Running in SYNCHRONOUS mode - this will block until complete');
            }

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
            $totalContactsCreated = 0;
            $totalContactsUpdated = 0;

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

                // Progress bar for synchronous mode
                if ($sync && $accounts->count() > 0) {
                    $this->output->newLine();
                    $progressBar = $this->output->createProgressBar($accounts->count());
                    $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
                    $progressBar->setMessage('Starting synchronization...');
                    $progressBar->start();
                }

                foreach ($accounts as $account) {
                    try {
                        if ($sync) {
                            // Count contacts before sync
                            $contactsBeforeSync = Contact::where('user_id', $user->id)->count();
                            
                            // Synchronous execution - run immediately
                            if (isset($progressBar)) {
                                $progressBar->setMessage("Syncing {$account->provider->value} account...");
                            }
                            
                            SyncContactsFromAccount::dispatch($account);
                            
                            // Count contacts after sync
                            $contactsAfterSync = Contact::where('user_id', $user->id)->count();
                            $contactsAdded = $contactsAfterSync - $contactsBeforeSync;
                            
                            $totalContactsCreated += max(0, $contactsAdded);
                            if ($contactsAdded > 0) {
                                $totalContactsUpdated += 0; // New contacts
                            } else {
                                $totalContactsUpdated += abs($contactsAdded); // Estimate updated
                            }
                            
                            if (isset($progressBar)) {
                                $progressBar->setMessage("✅ {$account->provider->value}: +{$contactsAdded} contacts");
                                $progressBar->advance();
                            } else {
                                $this->info("   ✅ Synced {$account->provider->value}: +{$contactsAdded} contacts");
                            }
                        } else {
                            // Asynchronous execution - queue the job
                            $this->contactSyncService->queueContactSync($account);
                            $this->line("   ✅ Queued sync for {$account->provider->value} account");
                        }
                        
                        $account->update(['last_sync_at' => now()]);
                        $totalSynced++;

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
                
                // Finish progress bar for synchronous mode
                if ($sync && isset($progressBar)) {
                    $progressBar->setMessage('Synchronization completed!');
                    $progressBar->finish();
                    $this->output->newLine(2);
                }
            }

            // Summary
            $this->output->newLine();
            $this->info('🎉 Synchronization completed!');
            $this->info("✅ Synced: {$totalSynced} accounts");
            $this->info("⏭️  Skipped: {$totalSkipped} accounts");

            if ($sync && $totalSynced > 0) {
                $this->output->newLine();
                $this->info('📊 Contact Statistics:');
                $this->line("   📥 New contacts: {$totalContactsCreated}");
                $this->line("   🔄 Processed: " . ($totalContactsCreated + $totalContactsUpdated));
            }

            if ($totalSynced > 0 && !$sync) {
                $this->info('💡 Contact sync jobs have been queued. Run queue worker to process them.');
            }

            // Detailed completion log
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            
            Log::info('✅ SCHEDULED SYNC COMPLETED SUCCESSFULLY', [
                'timestamp' => $endTime->toDateTimeString(),
                'duration_seconds' => $duration,
                'accounts_synced' => $totalSynced,
                'accounts_skipped' => $totalSkipped,
                'new_contacts' => $totalContactsCreated,
                'processed_contacts' => $totalContactsCreated + $totalContactsUpdated,
                'sync_mode' => $sync ? 'synchronous' : 'asynchronous',
            ]);
            Log::info('============================================');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Synchronization failed: ' . $e->getMessage());
            
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            
            Log::error('❌ SCHEDULED SYNC FAILED', [
                'timestamp' => $endTime->toDateTimeString(),
                'duration_seconds' => $duration,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            Log::info('============================================');

            return 1;
        }
    }
}
