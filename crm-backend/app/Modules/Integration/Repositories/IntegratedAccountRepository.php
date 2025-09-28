<?php

declare(strict_types=1);

namespace App\Modules\Integration\Repositories;

use App\Models\User;
use App\Modules\Integration\Contracts\IntegratedAccountRepositoryInterface;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IntegratedAccountRepository implements IntegratedAccountRepositoryInterface
{
    public function findByUser(User $user): Collection
    {
        return IntegratedAccount::where('user_id', $user->id)->get();
    }

    public function findActiveByUser(User $user): Collection
    {
        return IntegratedAccount::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();
    }

    public function findByUserAndProvider(User $user, string $provider): Collection
    {
        return IntegratedAccount::where('user_id', $user->id)
            ->where('provider', $provider)
            ->get();
    }

    public function findRecentByUser(User $user, int $minutes = 5): Collection
    {
        return IntegratedAccount::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->get();
    }

    public function createOrUpdate(array $data): IntegratedAccount
    {
        $uniqueFields = [
            'user_id' => $data['user_id'],
            'unipile_account_id' => $data['unipile_account_id'],
        ];

        $account = IntegratedAccount::updateOrCreate($uniqueFields, $data);

        Log::info('IntegratedAccountRepository: Account '.($account->wasRecentlyCreated ? 'created' : 'updated'), [
            'account_id' => $account->id,
            'provider' => $account->provider,
            'name' => $account->account_name,
        ]);

        return $account;
    }

    public function updateStatus(IntegratedAccount $account, string $status): bool
    {
        try {
            $oldStatus = $account->status;
            $account->update(['status' => $status]);

            Log::info('IntegratedAccountRepository: Status updated', [
                'account_id' => $account->id,
                'old_status' => $oldStatus,
                'new_status' => $status,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('IntegratedAccountRepository: Failed to update status', [
                'account_id' => $account->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(IntegratedAccount $account): bool
    {
        try {
            $account->delete();

            Log::info('IntegratedAccountRepository: Account deleted', [
                'account_id' => $account->id,
                'provider' => $account->provider,
                'name' => $account->account_name,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('IntegratedAccountRepository: Failed to delete account', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function findByUnipileId(string $unipileAccountId): ?IntegratedAccount
    {
        return IntegratedAccount::where('unipile_account_id', $unipileAccountId)->first();
    }
}
