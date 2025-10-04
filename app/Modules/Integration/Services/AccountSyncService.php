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
            Log::info('AccountSyncService: Starting sync for user', [
                'user_id' => $user->id,
            ]);

            $response = $this->unipileService->listAccounts();
            $accounts = $response['items'] ?? [];

            Log::info('AccountSyncService: Found accounts in provider', [
                'count' => count($accounts),
                'user_id' => $user->id,
            ]);

            $syncedAccounts = collect();
            $addedCount = 0;
            $updatedCount = 0;

            foreach ($accounts as $accountData) {
                // Create DTO from Unipile data
                $syncDto = SyncAccountDTO::fromUnipileData($user->id, $accountData);

                Log::info('AccountSyncService: Processing account', [
                    'provider' => $syncDto->provider,
                    'account_name' => $syncDto->accountName,
                    'unipile_id' => $syncDto->unipileAccountId,
                ]);

                // Find or create account using repository with DTO
                $account = $this->accountRepository->createOrUpdate($syncDto->toArray());

                if ($account->wasRecentlyCreated) {
                    $addedCount++;
                    Log::info('AccountSyncService: Created new account', [
                        'account_id' => $account->id,
                        'provider' => $syncDto->provider,
                        'name' => $syncDto->accountName,
                    ]);
                } else {
                    $updatedCount++;
                    Log::info('AccountSyncService: Updated existing account', [
                        'account_id' => $account->id,
                        'provider' => $syncDto->provider,
                        'name' => $syncDto->accountName,
                    ]);
                }

                $syncedAccounts->push($account);
            }

            Log::info('AccountSyncService: Sync completed', [
                'user_id' => $user->id,
                'total_accounts' => count($accounts),
                'added' => $addedCount,
                'updated' => $updatedCount,
            ]);

            return $syncedAccounts;

        } catch (\Exception $e) {
            Log::error('AccountSyncService: Sync failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString(),
            ]);

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
