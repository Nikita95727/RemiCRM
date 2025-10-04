<?php

declare(strict_types=1);

namespace App\Shared\Contracts;

use App\Modules\Contact\Models\Contact;
use App\Shared\DTOs\ContactDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ContactRepositoryInterface
{
    /**
     * @return LengthAwarePaginator<int, Contact>
     */
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator;

    /**
     * @return Collection<int, Contact>
     */
    public function getActive(): Collection;

    public function findById(int $id): ?Contact;

    public function findByEmail(string $email): ?Contact;

    public function findByPhone(string $phone): ?Contact;

    public function findByTelegramUsername(string $username): ?Contact;

    public function create(ContactDTO $contactDTO): Contact;

    public function update(int $id, ContactDTO $contactDTO): Contact;

    public function delete(int $id): bool;

    /**
     * @return Collection<int, Contact>
     */
    public function getBySource(string $source): Collection;

    /**
     * @return Collection<int, Contact>
     */
    public function searchByName(string $name): Collection;

    /**
     * @param array<array<string, mixed>> $contactsData
     * @return Collection<int, Contact>
     */
    public function createMany(array $contactsData): Collection;

    public function updateLastContactAt(int $id): bool;
}
