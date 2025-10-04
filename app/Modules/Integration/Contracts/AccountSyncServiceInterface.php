<?php

declare(strict_types=1);

namespace App\Modules\Integration\Contracts;

use App\Models\User;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;

interface AccountSyncServiceInterface
{
    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function syncFromProvider(User $user): Collection;

    public function updateAccountStatus(IntegratedAccount $account, string $status): bool;

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function getAccountsByProvider(User $user, string $provider): Collection;

    public function deleteAccount(IntegratedAccount $account): bool;
}
