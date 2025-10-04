<?php

namespace App\Console\Commands;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;

class SyncUnipileAccounts extends Command
{
    protected $signature = 'unipile:sync-accounts {user_id : User ID to sync accounts for}';

    protected $description = 'Sync connected accounts from Unipile API to local database';

    public function __construct(
        private UnipileService $unipileService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $userId = $this->argument('user_id');

        try {
            $response = $this->unipileService->listAccounts();
            $accounts = $response['items'] ?? [];

            foreach ($accounts as $account) {
                $accountId = $account['id'];
                $provider = $account['type'];
                $name = $account['name'] ?? 'Unknown';
                $status = 'active';

                if (! empty($account['sources'])) {
                    $status = $account['sources'][0]['status'] === 'OK' ? 'active' : 'error';
                }

                $existingAccount = IntegratedAccount::where('unipile_account_id', $accountId)
                    ->where('user_id', $userId)
                    ->first();

                if ($existingAccount) {
                    $existingAccount->update([
                        'provider' => strtolower($provider),
                        'account_name' => $name,
                        'status' => $status,
                        'last_sync_at' => now(),
                        'metadata' => $account,
                    ]);
                } else {
                    IntegratedAccount::create([
                        'user_id' => $userId,
                        'provider' => strtolower($provider),
                        'unipile_account_id' => $accountId,
                        'account_name' => $name,
                        'status' => $status,
                        'last_sync_at' => now(),
                        'sync_enabled' => true,
                        'metadata' => $account,
                    ]);
                }
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Sync failed: '.$e->getMessage());

            return 1;
        }
    }
}
