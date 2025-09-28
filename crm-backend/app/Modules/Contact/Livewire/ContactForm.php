<?php

declare(strict_types=1);

namespace App\Modules\Contact\Livewire;

use App\Modules\Contact\Models\Contact;
use App\Shared\Enums\ContactSource;
use Livewire\Component;

class ContactForm extends Component
{
    public bool $isOpen = false;

    public bool $isEdit = false;

    public ?int $contactId = null;

    // Form fields
    public string $name = '';

    public string $phone = '';

    public string $email = '';

    /** @var array<string> */
    public array $selectedSources = ['crm'];

    public string $notes = '';

    public string $tagsInput = '';

    /** @var array<string, string> */
    protected $listeners = [
        'openContactForm' => 'openForm',
        'editContact' => 'editContact',
    ];

    /** @var array<string, string> */
    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'email' => 'nullable|email|max:255',
        'selectedSources' => 'required|array|min:1',
        'selectedSources.*' => 'in:crm,telegram,whatsapp,gmail',
        'notes' => 'nullable|string|max:1000',
        'tagsInput' => 'nullable|string|max:500',
    ];

    /** @var array<string, string> */
    protected $messages = [
        'name.required' => 'Name is required.',
        'name.max' => 'Name cannot be longer than 255 characters.',
        'phone.max' => 'Phone cannot be longer than 20 characters.',
        'email.email' => 'Please enter a valid email address.',
        'email.max' => 'Email cannot be longer than 255 characters.',
        'selectedSources.required' => 'At least one source is required.',
        'selectedSources.min' => 'At least one source must be selected.',
        'notes.max' => 'Notes cannot be longer than 1000 characters.',
        'tagsInput.max' => 'Tags cannot be longer than 500 characters.',
    ];

    public function openForm(): void
    {
        $this->resetForm();
        $this->isEdit = false;
        $this->isOpen = true;
        $this->dispatch('modal-opened');
    }

    public function editContact(int $contactId): void
    {
        $contact = Contact::find($contactId);

        if (! $contact) {
            return;
        }

        $this->contactId = $contact->id;
        $this->name = $contact->name;
        $this->phone = $contact->phone ?? '';
        $this->email = $contact->email ?? '';
        $this->selectedSources = $contact->sources ?? ['crm'];
        $this->notes = $contact->notes ?? '';
        $this->tagsInput = $contact->tags ? implode(', ', $contact->tags) : '';

        $this->isEdit = true;
        $this->isOpen = true;
        $this->dispatch('modal-opened');
    }

    public function closeForm(): void
    {
        $this->isOpen = false;
        $this->resetForm();
        $this->dispatch('modal-closed');
    }

    public function save(): void
    {
        $this->validate();

        $tags = $this->tagsInput
            ? array_filter(array_map('trim', explode(',', $this->tagsInput)))
            : null;

        $data = [
            'name' => $this->name,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'sources' => $this->selectedSources,
            'notes' => $this->notes ?: null,
            'tags' => $tags,
            'user_id' => auth()->id(),
        ];

        if ($this->isEdit && $this->contactId) {
            $contact = Contact::find($this->contactId);
            $contact->update($data);
            $message = 'Contact updated successfully!';
        } else {
            Contact::create($data);
            $message = 'Contact created successfully!';
        }

        $this->dispatch('contactSaved', $message);
        $this->dispatch('refreshContacts');
        $this->closeForm();

        session()->flash('success', $message);
    }

    private function resetForm(): void
    {
        $this->contactId = null;
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->selectedSources = ['crm'];
        $this->notes = '';
        $this->tagsInput = '';
        $this->resetValidation();
    }

    /**
     * @return array<string, string>
     */
    public function getSourcesProperty(): array
    {
        return ContactSource::labels();
    }

    /**
     * @return array<string, ContactSource>
     */
    public function getSourceObjectsProperty(): array
    {
        $objects = [];
        foreach (ContactSource::cases() as $source) {
            $objects[$source->value] = $source;
        }

        return $objects;
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('livewire.contact.contact-form', [
            'sources' => $this->sources,
            'sourceObjects' => $this->sourceObjects,
        ]);
    }
}
