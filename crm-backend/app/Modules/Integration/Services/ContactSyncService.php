<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Modules\Integration\Contracts\ContactSyncServiceInterface;
use App\Modules\Integration\Jobs\SyncContactsFromAccount;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ContactSyncService implements ContactSyncServiceInterface
{
    public function syncContactsFromAccount(IntegratedAccount $account): Collection
    {
        Log::info('ContactSyncService: Direct sync not implemented, use queueContactSync instead', [
            'account_id' => $account->id,
        ]);

        return collect();
    }

    public function queueContactSync(IntegratedAccount $account): void
    {
        Log::info('ContactSyncService: Queueing contact sync', [
            'account_id' => $account->id,
            'provider' => $account->provider,
            'account_name' => $account->account_name,
        ]);

        SyncContactsFromAccount::dispatch($account);
    }

    public function getSyncStatistics(IntegratedAccount $account): array
    {
        $contactsCount = $account->user->contacts()
            ->whereJsonContains('sources', $account->provider)
            ->count();

        return [
            'account_id' => $account->id,
            'provider' => $account->provider,
            'contacts_synced' => $contactsCount,
            'last_sync_at' => $account->last_sync_at,
            'status' => $account->status,
        ];
    }
}
