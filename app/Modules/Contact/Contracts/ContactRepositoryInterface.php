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

    /**
     * Find contact by ID for a specific user
     */
    public function findByUserAndId(User $user, int $contactId): ?Contact;

    /**
     * Get paginated contacts with search and filters
     * @param array<string> $sourceFilters
     * @return LengthAwarePaginator<int, Contact>
     */
    public function paginateWithFilters(User $user, ?string $search = null, array $sourceFilters = [], string $sortBy = 'created_at', string $sortDirection = 'desc', int $perPage = 15): LengthAwarePaginator;

    /**
     * Get contact statistics by source
     * @return array<string, int>
     */
    public function getContactStatsByUser(User $user): array;

    /**
     * Find contacts without tags for a specific account
     * @return Collection<int, Contact>
     */
    public function findUntaggedByAccount(int $accountId, int $userId): Collection;

    /**
     * Find contact by email or phone for user
     */
    public function findByEmailOrPhone(User $user, ?string $email, ?string $phone): ?Contact;

    /**
     * Find contact by name for user (fallback)
     */
    public function findByName(User $user, string $name): ?Contact;

    /**
     * Find contact by provider_id for user
     */
    public function findByProviderId(User $user, string $providerId): ?Contact;
}
