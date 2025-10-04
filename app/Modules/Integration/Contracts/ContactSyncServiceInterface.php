<?php

declare(strict_types=1);

namespace App\Modules\Integration\Contracts;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Contact\DTOs\CreateContactDTO;
use Illuminate\Support\Collection;

interface ContactSyncServiceInterface
{
    /**
     * @return Collection<int, CreateContactDTO>
     */
    public function syncContactsFromAccount(IntegratedAccount $account): Collection;

    public function queueContactSync(IntegratedAccount $account): void;

    /**
     * @return array<string, mixed>
     */
    public function getSyncStatistics(IntegratedAccount $account): array;
}
