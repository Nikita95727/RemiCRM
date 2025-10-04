<?php

declare(strict_types=1);

namespace App\Modules\Contact\Livewire;

use App\Models\ImportStatus;
use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Contact\Models\Contact;
use App\Shared\Enums\ContactSource;
use Livewire\Component;
use Livewire\WithPagination;

class ContactsList extends Component
{
    use WithPagination;

    public string $search = '';

    /** @var array<string> */
    public array $sourceFilters = [];

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public bool $showDeleteModal = false;

    public ?int $contactToDelete = null;

    public string $contactToDeleteName = '';

    public ?Contact $viewingContact = null;

    public bool $isPolling = false;

    private int $lastContactCount = 0;

    /** @var array<string, mixed> */
    protected $queryString = [
        'search' => ['except' => ''],
        'sourceFilters' => ['except' => []],
    ];

    /** @var array<string, string> */
    protected $listeners = [
        'refreshContacts' => '$refresh',
        'contactSaved' => 'handleContactSaved',
        'contactsSynced' => '$refresh',
        'start-polling' => 'startPolling',
        'check-new-contacts' => 'checkForNewContacts',
        'stop-polling' => 'stopPolling',
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSourceFilters(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    /**
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, \App\Modules\Contact\Models\Contact>
     */
    public function getContactsProperty()
    {
        $user = auth()->user();
        
        if (!$user) {
            return Contact::query()->where('id', 0)->paginate(15); // Empty result
        }

        /** @var ContactRepositoryInterface $contactRepository */
        $contactRepository = app(ContactRepositoryInterface::class);

        return $contactRepository->paginateWithFilters(
            $user,
            $this->search ?: null,
            $this->sourceFilters,
            $this->sortBy,
            $this->sortDirection,
            15
        );
    }

    /**
     * Get contact count by source for stats
     */
    public function getContactStatsProperty(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [
                'crm' => 0,
                'telegram' => 0,
                'whatsapp' => 0,
                'gmail' => 0,
                'total' => 0
            ];
        }

        /** @var ContactRepositoryInterface $contactRepository */
        $contactRepository = app(ContactRepositoryInterface::class);

        return $contactRepository->getContactStatsByUser($user);
    }

    /**
     * Get active import status for current user
     */
    public function getImportStatusProperty(): ?ImportStatus
    {
        $user = auth()->user();
        
        if (!$user) {
            return null;
        }

        return ImportStatus::where('user_id', $user->id)
            ->whereIn('status', ['importing', 'tagging', 'completed'])
            ->where('updated_at', '>=', now()->subSeconds(30)) // Show completed status for max 30 seconds
            ->orderBy('updated_at', 'desc')
            ->first();
    }

    public function openConnectModal(): void
    {
        $this->dispatch('openConnectModal');
    }

    public function editContact(int $contactId): void
    {
        $this->dispatch('editContact', $contactId);
    }

    public function viewContact(int $contactId): void
    {
        $user = auth()->user();
        if (!$user) {
            return;
        }

        /** @var ContactRepositoryInterface $contactRepository */
        $contactRepository = app(ContactRepositoryInterface::class);
        
        $this->viewingContact = $contactRepository->findByUserAndId($user, $contactId);
    }

    public function closeViewModal(): void
    {
        $this->viewingContact = null;
    }

    public function openContactForm(): void
    {
        $this->dispatch('openContactForm');
    }

    public function handleContactSaved(string $message): void
    {
        session()->flash('success', $message);
        // Reset to first page to ensure new contact is visible
        $this->resetPage();
        // Force refresh the component
        $this->dispatch('$refresh');
    }

    public function confirmDelete(int $contactId): void
    {
        $contact = Contact::find($contactId);

        if ($contact) {
            $this->contactToDelete = $contactId;
            $this->contactToDeleteName = $contact->name;
            $this->showDeleteModal = true;
        }
    }

    public function deleteContact(): void
    {
        if ($this->contactToDelete) {
            $contact = Contact::find($this->contactToDelete);

            if ($contact) {
                $contact->delete(); // Soft delete
                session()->flash('success', 'Contact deleted successfully.');
            }
        }

        $this->cancelDelete();
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->contactToDelete = null;
        $this->contactToDeleteName = '';
    }

    public function startPolling(): void
    {
        $userId = auth()->id();
        if (!$userId) {
            return;
        }
        
        $this->isPolling = true;
        $this->lastContactCount = Contact::where('user_id', $userId)->count();
    }

    public function stopPolling(): void
    {
        $this->isPolling = false;
    }

    public function checkForNewContacts(): void
    {
        if (! $this->isPolling) {
            return;
        }

        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        $currentCount = Contact::where('user_id', $userId)->count();

        if ($currentCount > $this->lastContactCount) {
            $this->lastContactCount = $currentCount;
            $this->dispatch('contactsSynced');
            $this->stopPolling();

            // Show success message
            session()->flash('success', 'New contacts have been synchronized!');
        }
    }

    public function manualSync(): void
    {
        try {
            $user = auth()->user();
            if (! $user) {
                session()->flash('error', 'Authentication required.');

                return;
            }

            // Get active integrated accounts
            $accounts = \App\Modules\Integration\Models\IntegratedAccount::where('user_id', $user->id)
                ->where('status', 'active')
                ->get();

            if ($accounts->isEmpty()) {
                session()->flash('error', 'No connected accounts found. Please connect an account first.');

                return;
            }

            // Use the unified ContactSyncService for all synchronization
            $contactSyncService = app(\App\Modules\Integration\Contracts\ContactSyncServiceInterface::class);

            foreach ($accounts as $account) {
                $contactSyncService->queueContactSync($account);
            }

            session()->flash('success', 'Manual synchronization started! New contacts will appear shortly.');

            // Start polling to check for new contacts
            $this->startPolling();

        } catch (\Exception $e) {
            \Log::error('Manual sync error: '.$e->getMessage());
            session()->flash('error', 'Synchronization failed. Please try again.');
        }
    }

    /**
     * @return array<string, string>
     */
    public function getSourcesProperty(): array
    {
        return ContactSource::labels();
    }

    public function getHasActiveAccountsProperty(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return \App\Modules\Integration\Models\IntegratedAccount::where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.contact.contacts-list', [
            'contacts' => $this->contacts,
            'sources' => $this->sources,
            'hasActiveAccounts' => $this->hasActiveAccounts,
        ]);
    }
}
