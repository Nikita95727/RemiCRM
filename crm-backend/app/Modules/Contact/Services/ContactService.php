<?php

declare(strict_types=1);

namespace App\Modules\Contact\Services;

use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Contact\DTOs\CreateContactDTO;
use App\Modules\Contact\Models\Contact;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Contact Service
 * 
 * Handles business logic for contact management operations.
 * Implements the Service Layer pattern for clean separation of concerns.
 */
final class ContactService
{
    public function __construct(
        private readonly ContactRepositoryInterface $contactRepository
    ) {}

    /**
     * Get paginated contacts for a user
     * 
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator<int, Contact>
     */
    public function getPaginatedContacts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->contactRepository->paginateByUser($user, $perPage);
    }

    /**
     * Get all contacts for a user
     * 
     * @param User $user
     * @return Collection<int, Contact>
     */
    public function getAllContacts(User $user): Collection
    {
        return $this->contactRepository->findByUser($user);
    }

    /**
     * Find contact by ID for a specific user
     */
    public function findContactById(int $contactId, User $user): ?Contact
    {
        return $this->contactRepository->findByUserAndId($user, $contactId);
    }

    /**
     * Create a new contact
     */
    public function createContact(CreateContactDTO $dto): Contact
    {
        return $this->contactRepository->create($dto->toArray());
    }

    /**
     * Update an existing contact
     * 
     * @param Contact $contact
     * @param array<string, mixed> $data
     */
    public function updateContact(Contact $contact, array $data): Contact
    {
        $this->contactRepository->update($contact, $data);
        return $contact->fresh() ?? $contact;
    }

    /**
     * Delete a contact (soft delete)
     */
    public function deleteContact(Contact $contact): bool
    {
        return $this->contactRepository->delete($contact);
    }

    /**
     * Search contacts by query
     * 
     * @param User $user
     * @param string $query
     * @return Collection<int, Contact>
     */
    public function searchContacts(User $user, string $query): Collection
    {
        return $this->contactRepository->search($user, $query);
    }

    /**
     * Get contacts by source
     * 
     * @param User $user
     * @param array<string> $sources
     * @return Collection<int, Contact>
     */
    public function getContactsBySources(User $user, array $sources): Collection
    {
        return $this->contactRepository->findByUserAndSources($user, $sources);
    }

    /**
     * Get contact statistics for a user
     * 
     * @param User $user
     * @return array<string, mixed>
     */
    public function getContactStatistics(User $user): array
    {
        $allContacts = $this->contactRepository->findByUser($user);
        
        $stats = [
            'total' => $allContacts->count(),
            'by_source' => [],
            'recent_count' => 0,
        ];

        // Count by sources
        foreach ($allContacts as $contact) {
            $sources = $contact->sources ?? [];
            foreach ($sources as $source) {
                $stats['by_source'][$source] = ($stats['by_source'][$source] ?? 0) + 1;
            }
        }

        // Count recent contacts (last 7 days)
        $stats['recent_count'] = $allContacts->where('created_at', '>=', now()->subWeek())->count();

        return $stats;
    }

    /**
     * Bulk create contacts
     * 
     * @param array<CreateContactDTO> $contacts
     * @return Collection<int, Contact>
     */
    public function bulkCreateContacts(array $contacts): Collection
    {
        $contactsData = array_map(
            fn(CreateContactDTO $dto): array => $dto->toArray(),
            $contacts
        );

        return $this->contactRepository->createMany($contactsData);
    }

    /**
     * Check if contact exists by provider ID
     */
    public function contactExistsByProviderId(User $user, string $providerId, string $provider): bool
    {
        return $this->contactRepository->existsByProviderId($user, $providerId, $provider);
    }
}
