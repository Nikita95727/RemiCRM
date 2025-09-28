<?php

declare(strict_types=1);

namespace App\Modules\Contact\Contracts;

use App\Models\User;
use App\Modules\Contact\Models\Contact;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ContactRepositoryInterface
{
    /**
     * @return Collection<int, Contact>
     */
    public function findByUser(User $user): Collection;

    /**
     * @return LengthAwarePaginator<int, Contact>
     */
    public function paginateByUser(User $user, int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Contact>
     */
    public function findByUserAndSource(User $user, string $source): Collection;

    public function countByUserAndSource(User $user, string $source): int;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Contact;

    /**
     * @param array<string, mixed> $data
     */
    public function update(Contact $contact, array $data): bool;

    public function delete(Contact $contact): bool;

    /**
     * @return Collection<int, Contact>
     */
    public function search(User $user, string $query): Collection;

    /**
     * @param array<string> $sources
     * @return Collection<int, Contact>
     */
    public function findByUserAndSources(User $user, array $sources): Collection;
}
