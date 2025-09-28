<?php

declare(strict_types=1);

namespace App\Modules\Contact\Livewire;

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
        $query = Contact::query()
            ->where('user_id', auth()->id())
            ->when($this->search, fn ($q) => $q->search($this->search))
            ->when(! empty($this->sourceFilters), function ($q) {
                $q->where(function ($subQuery) {
                    foreach ($this->sourceFilters as $source) {
                        $subQuery->orWhereJsonContains('sources', $source);
                    }
                });
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    public function openConnectModal(): void
    {
        $this->dispatch('openConnectModal');
    }

    public function editContact(int $contactId): void
    {
        $this->dispatch('editContact', $contactId);
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
        $this->isPolling = true;
        $this->lastContactCount = Contact::where('user_id', auth()->id())->count();
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

        $currentCount = Contact::where('user_id', auth()->id())->count();

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
