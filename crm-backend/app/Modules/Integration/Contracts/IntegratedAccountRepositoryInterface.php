<?php

declare(strict_types=1);

namespace App\Modules\Integration\Contracts;

use App\Models\User;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;

interface IntegratedAccountRepositoryInterface
{
    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function findByUser(User $user): Collection;

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function findActiveByUser(User $user): Collection;

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function findByUserAndProvider(User $user, string $provider): Collection;

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function findRecentByUser(User $user, int $minutes = 5): Collection;

    /**
     * @param array<string, mixed> $data
     */
    public function createOrUpdate(array $data): IntegratedAccount;

    public function updateStatus(IntegratedAccount $account, string $status): bool;

    public function delete(IntegratedAccount $account): bool;

    public function findByUnipileId(string $unipileAccountId): ?IntegratedAccount;
}
