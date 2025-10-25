<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Integration\Contracts\ContactSyncServiceInterface;
use App\Modules\Integration\Jobs\SyncContactsFromAccount;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ContactSyncService implements ContactSyncServiceInterface
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository
    ) {}
    public function syncContactsFromAccount(IntegratedAccount $account): Collection
    {
        return collect();
    }

    public function queueContactSync(IntegratedAccount $account): void
    {
        // IMMEDIATE SYNC for testing - run synchronously instead of queuing
        Log::info('ContactSyncService: Running IMMEDIATE sync (not queued)', [
            'account_id' => $account->id,
            'provider' => $account->provider,
        ]);
        
        // Run the job immediately instead of dispatching to queue
        $job = new SyncContactsFromAccount($account);
        $job->handle(
            app(\App\Modules\Integration\Services\UnipileService::class),
            app(\App\Modules\Contact\Contracts\ContactRepositoryInterface::class)
        );
        
        Log::info('ContactSyncService: IMMEDIATE sync completed', [
            'account_id' => $account->id,
            'provider' => $account->provider,
        ]);
    }

    public function getSyncStatistics(IntegratedAccount $account): array
    {
        $contactsCount = $this->contactRepository->countByUserAndSource(
            $account->user,
            $account->provider->value
        );

        return [
            'account_id' => $account->id,
            'provider' => $account->provider,
            'contacts_synced' => $contactsCount,
            'last_sync_at' => $account->last_sync_at,
            'status' => $account->status,
        ];
    }
}
