<?php

declare(strict_types=1);

namespace App\Modules\Contact\Livewire;

use App\Modules\Contact\Models\Contact;
use Livewire\Component;

class GlobalSearch extends Component
{
    public string $search = '';

    public bool $isOpen = false;

    /** @var array<int, array<string, mixed>> */
    public array $results = [];

    public int $selectedIndex = -1;

    /** @var array<string, string> */
    protected $listeners = [
        'openSearch' => 'openModal',
        'closeSearch' => 'closeModal',
    ];

    public function updatedSearch(): void
    {
        $this->selectedIndex = -1;

        // Clear results if search is empty or too short
        if (empty($this->search) || strlen(trim($this->search)) < 2) {
            $this->results = [];

            return;
        }

        try {
            $contacts = Contact::where('user_id', auth()->id())
                ->search(trim($this->search))
                ->limit(8)
                ->get();

            $results = [];
            foreach ($contacts as $contact) {
                $primarySource = $contact->primarySource;
                $results[] = [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                    'sources' => $contact->sourceObjects,
                    'primary_source' => $primarySource ? $primarySource->getLabel() : 'No source',
                    'primary_source_color' => $primarySource ? $primarySource->getCssClass() : 'bg-slate-100 text-slate-600',
                    'tags' => $contact->tags ?? [],
                    'initials' => $contact->initials,
                    'updated_at' => $contact->updated_at?->format('M j, Y'),
                ];
            }
            $this->results = $results;
        } catch (\Exception $e) {
            // Log error and clear results
            \Log::error('GlobalSearch error: '.$e->getMessage());
            $this->results = [];
        }
    }

    public function openModal(): void
    {
        $this->isOpen = true;
        $this->search = '';
        $this->results = [];
        $this->selectedIndex = -1;
    }

    public function closeModal(): void
    {
        $this->isOpen = false;
        $this->search = '';
        $this->results = [];
        $this->selectedIndex = -1;
    }

    public function selectNext(): void
    {
        if ($this->selectedIndex < count($this->results) - 1) {
            $this->selectedIndex++;
        }
    }

    public function selectPrevious(): void
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    public function selectContact(int $index): void
    {
        if (isset($this->results[$index])) {
            $contactId = $this->results[$index]['id'];
            $this->closeModal();
            // Here you could redirect to contact details or emit event
            $this->dispatch('contactSelected', $contactId);
        }
    }

    public function selectCurrentContact(): void
    {
        if ($this->selectedIndex >= 0 && isset($this->results[$this->selectedIndex])) {
            $this->selectContact($this->selectedIndex);
        }
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.contact.global-search');
    }
}
