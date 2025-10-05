<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Models\User;
use App\Modules\Integration\Contracts\AccountSyncServiceInterface;
use App\Modules\Integration\Contracts\IntegratedAccountRepositoryInterface;
use App\Modules\Integration\DTOs\SyncAccountDTO;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AccountSyncService implements AccountSyncServiceInterface
{
    public function __construct(
        private UnipileService $unipileService,
        private IntegratedAccountRepositoryInterface $accountRepository
    ) {}

    public function syncFromProvider(User $user): Collection
    {
        try {
            // IMPORTANT: Filter accounts by user ID to prevent syncing all accounts
            $response = $this->unipileService->listAccounts((string) $user->id);
            $accounts = $response['items'] ?? [];

            $syncedAccounts = collect();

            foreach ($accounts as $accountData) {
                $syncDto = SyncAccountDTO::fromUnipileData($user->id, $accountData);
                $account = $this->accountRepository->createOrUpdate($syncDto->toArray());
                $syncedAccounts->push($account);
            }

            return $syncedAccounts;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function updateAccountStatus(IntegratedAccount $account, string $status): bool
    {
        return $this->accountRepository->updateStatus($account, $status);
    }

    public function getAccountsByProvider(User $user, string $provider): Collection
    {
        return $this->accountRepository->findByUserAndProvider($user, $provider);
    }

    public function deleteAccount(IntegratedAccount $account): bool
    {
        return $this->accountRepository->delete($account);
    }
}
