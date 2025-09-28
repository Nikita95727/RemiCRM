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
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('notes', 'like', "%{$query}%");
            })
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
}
