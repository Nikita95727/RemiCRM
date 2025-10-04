<?php

declare(strict_types=1);

namespace App\Modules\Contact\Repositories;

use App\Models\User;
use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Contact\Models\Contact;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ContactRepository implements ContactRepositoryInterface
{
    /**
     * @return Collection<int, Contact>
     */
    public function findByUser(User $user): Collection
    {
        return Contact::where('user_id', $user->id)->get();
    }

    /**
     * @return LengthAwarePaginator<int, Contact>
     */
    public function paginateByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Contact::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, Contact>
     */
    public function findByUserAndSource(User $user, string $source): Collection
    {
        return Contact::where('user_id', $user->id)
            ->whereJsonContains('sources', $source)
            ->get();
    }

    public function countByUserAndSource(User $user, string $source): int
    {
        return Contact::where('user_id', $user->id)
            ->whereJsonContains('sources', $source)
            ->count();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Contact
    {
        $contact = Contact::create($data);

        Log::info('ContactRepository: Contact created', [
            'contact_id' => $contact->id,
            'name' => $contact->name,
            'sources' => $contact->sources,
        ]);

        return $contact;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Contact $contact, array $data): bool
    {
        try {
            $contact->update($data);

            Log::info('ContactRepository: Contact updated', [
                'contact_id' => $contact->id,
                'name' => $contact->name,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('ContactRepository: Failed to update contact', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function delete(Contact $contact): bool
    {
        try {
            $contact->delete();

            Log::info('ContactRepository: Contact deleted', [
                'contact_id' => $contact->id,
                'name' => $contact->name,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('ContactRepository: Failed to delete contact', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * @return Collection<int, Contact>
     */
    public function search(User $user, string $query): Collection
    {
        return Contact::where('user_id', $user->id)
            ->search($query)
            ->get();
    }

    /**
     * @param array<string> $sources
     * @return Collection<int, Contact>
     */
    public function findByUserAndSources(User $user, array $sources): Collection
    {
        $query = Contact::where('user_id', $user->id);

        foreach ($sources as $source) {
            $query->orWhereJsonContains('sources', $source);
        }

        return $query->get();
    }

    /**
     * Find contact by ID for a specific user
     */
    public function findByUserAndId(User $user, int $contactId): ?Contact
    {
        return Contact::where('user_id', $user->id)
            ->where('id', $contactId)
            ->first();
    }

    /**
     * Get paginated contacts with search and filters - OPTIMIZED
     * @param array<string> $sourceFilters
     * @return LengthAwarePaginator<int, Contact>
     */
    public function paginateWithFilters(User $user, ?string $search = null, array $sourceFilters = [], string $sortBy = 'created_at', string $sortDirection = 'desc', int $perPage = 15): LengthAwarePaginator
    {
        $query = Contact::select(['id', 'name', 'email', 'phone', 'sources', 'tags', 'notes', 'created_at', 'updated_at'])
            ->where('user_id', $user->id)
            ->when($search, fn ($q) => $q->search($search))
            ->when(!empty($sourceFilters), function ($q) use ($sourceFilters) {
                $q->where(function ($subQuery) use ($sourceFilters) {
                    foreach ($sourceFilters as $source) {
                        $subQuery->orWhereJsonContains('sources', $source);
                    }
                });
            })
            ->orderBy($sortBy, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get contact statistics by source - OPTIMIZED
     * @return array<string, int>
     */
    public function getContactStatsByUser(User $user): array
    {
        // Single query with JSON extraction for better performance
        $stats = Contact::selectRaw('
            COUNT(*) as total,
            SUM(JSON_CONTAINS(sources, \'["crm"]\')) as crm,
            SUM(JSON_CONTAINS(sources, \'["telegram"]\')) as telegram,
            SUM(JSON_CONTAINS(sources, \'["whatsapp"]\')) as whatsapp,
            SUM(JSON_CONTAINS(sources, \'["google_oauth"]\')) as gmail
        ')
        ->where('user_id', $user->id)
        ->first();

        return [
            'crm' => (int) ($stats->crm ?? 0),
            'telegram' => (int) ($stats->telegram ?? 0),
            'whatsapp' => (int) ($stats->whatsapp ?? 0),
            'gmail' => (int) ($stats->gmail ?? 0),
            'total' => (int) ($stats->total ?? 0),
        ];
    }

    /**
     * Find contacts without tags for a specific account - OPTIMIZED
     * @return Collection<int, Contact>
     */
    public function findUntaggedByAccount(int $accountId, int $userId): Collection
    {
        return Contact::select(['id', 'name', 'email', 'phone', 'tags'])
            ->where('user_id', $userId)
            ->where(function ($query) {
                $query->whereNull('tags')
                    ->orWhere('tags', '[]')
                    ->orWhereJsonLength('tags', 0);
            })
            ->whereHas('integrations', function ($query) use ($accountId) {
                $query->where('integrated_account_id', $accountId);
            })
            ->with(['integrations' => function ($query) use ($accountId) {
                $query->where('integrated_account_id', $accountId)
                    ->select(['id', 'contact_id', 'integrated_account_id', 'external_id']);
            }])
            ->get();
    }

    /**
     * Find contact by email or phone for user - OPTIMIZED
     */
    public function findByEmailOrPhone(User $user, ?string $email, ?string $phone): ?Contact
    {
        return Contact::where('user_id', $user->id)
            ->where(function ($query) use ($email, $phone) {
                if ($email) {
                    $query->where('email', $email);
                }
                if ($phone) {
                    $query->orWhere('phone', $phone);
                }
            })
            ->first();
    }

    /**
     * Find contact by name for user (fallback) - OPTIMIZED
     */
    public function findByName(User $user, string $name): ?Contact
    {
        return Contact::where('user_id', $user->id)
            ->where('name', $name)
            ->first();
    }

    /**
     * Find contact by provider_id for user - OPTIMIZED
     */
    public function findByProviderId(User $user, string $providerId): ?Contact
    {
        return Contact::where('user_id', $user->id)
            ->where('provider_id', $providerId)
            ->first();
    }
}
