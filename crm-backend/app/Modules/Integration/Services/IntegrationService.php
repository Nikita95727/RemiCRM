<?php

declare(strict_types=1);

namespace App\Modules\Integration\Services;

use App\Models\User;
use App\Modules\Integration\Contracts\AccountSyncServiceInterface;
use App\Modules\Integration\Contracts\ContactSyncServiceInterface;
use App\Modules\Integration\Contracts\IntegratedAccountRepositoryInterface;
use App\Modules\Integration\Contracts\IntegrationServiceInterface;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IntegrationService implements IntegrationServiceInterface
{
    public function __construct(
        private AccountSyncServiceInterface $accountSyncService,
        private ContactSyncServiceInterface $contactSyncService,
        private IntegratedAccountRepositoryInterface $accountRepository,
        private UnipileService $unipileService
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function checkIntegrationStatus(User $user): array
    {
        try {
            Log::info('IntegrationService: Checking integration status', [
                'user_id' => $user->id,
            ]);

            // Sync accounts from provider
            $syncedAccounts = $this->syncAccountsFromProvider($user);

            // Get active accounts
            $activeAccounts = $this->getActiveAccounts($user);

            if ($activeAccounts->count() > 0) {
                // Filter only newly created accounts from this sync session
                $newAccounts = $syncedAccounts->filter(function ($account) {
                    return $account->wasRecentlyCreated;
                });

                Log::info('IntegrationService: Found accounts, filtering for new ones', [
                    'total_active_accounts' => $activeAccounts->count(),
                    'new_accounts' => $newAccounts->count(),
                    'new_account_names' => $newAccounts->pluck('account_name')->toArray(),
                ]);

                // Queue contact sync ONLY for newly added accounts
                foreach ($newAccounts as $account) {
                    Log::info('IntegrationService: Starting sync for new account', [
                        'account_id' => $account->id,
                        'provider' => $account->provider,
                        'account_name' => $account->account_name,
                    ]);
                    $this->initiateContactSync($account);
                }

                return [
                    'status' => 'success',
                    'message' => $newAccounts->count() > 0 
                        ? "New account connected! Contacts from {$newAccounts->first()->provider->value} are being synchronized."
                        : 'Integration completed successfully!',
                    'accounts_count' => $activeAccounts->count(),
                    'new_accounts_count' => $newAccounts->count(),
                    'redirect' => route('contacts'),
                ];
            }

            return [
                'status' => 'pending',
                'message' => 'Integration still in progress...',
            ];

        } catch (\Exception $e) {
            Log::error('IntegrationService: Error checking integration status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id,
            ]);

            return [
                'status' => 'error',
                'message' => 'Error checking integration status',
            ];
        }
    }

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function syncAccountsFromProvider(User $user): Collection
    {
        return $this->accountSyncService->syncFromProvider($user);
    }

    public function initiateContactSync(IntegratedAccount $account): void
    {
        Log::info('IntegrationService: Initiating contact sync', [
            'account_id' => $account->id,
            'provider' => $account->provider,
        ]);

        $this->contactSyncService->queueContactSync($account);
    }

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function getActiveAccounts(User $user): Collection
    {
        return $this->accountRepository->findActiveByUser($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function createHostedAuthLink(string $provider, User $user): array
    {
        $redirectUrl = route('integration.waiting');

        Log::info('IntegrationService: Creating hosted auth link', [
            'user_id' => $user->id,
            'provider' => $provider,
            'redirect_url' => $redirectUrl,
        ]);

        // No notify URL needed - we use webhook-free integration
        return $this->unipileService->createHostedAuthLink(
            [strtoupper($provider)],
            (string) $user->id,
            null, // No webhook notification needed
            $redirectUrl
        );
    }
}
