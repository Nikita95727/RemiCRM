<?php

declare(strict_types=1);

namespace App\Modules\Integration\Contracts;

use App\Models\User;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Support\Collection;

interface IntegrationServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function checkIntegrationStatus(User $user): array;

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function syncAccountsFromProvider(User $user): Collection;

    public function initiateContactSync(IntegratedAccount $account): void;

    /**
     * @return Collection<int, IntegratedAccount>
     */
    public function getActiveAccounts(User $user): Collection;

    /**
     * @return array<string, mixed>
     */
    public function createHostedAuthLink(string $provider, User $user): array;
}
