<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Contact\Livewire\ContactsList;
use App\Modules\Contact\Livewire\GlobalSearch;
use App\Modules\Contact\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LivewireComponentsTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function contacts_list_component_renders_correctly()
    {

        Contact::factory()->count(3)->create();

        Livewire::test(ContactsList::class)
            ->assertViewIs('livewire.contact.contacts-list')
            ->assertSee('Contacts')
            ->assertSet('contacts.total', 3);
    }

    /** @test */
    public function contacts_list_search_updates_results()
    {

        Contact::factory()->create(['name' => 'John Crypto', 'tags' => ['crypto']]);
        Contact::factory()->create(['name' => 'Jane Business', 'tags' => ['business']]);

        Livewire::test(ContactsList::class)
            ->set('search', 'John')
            ->assertSee('John Crypto')
            ->assertDontSee('Jane Business');
    }

    /** @test */
    public function contacts_list_source_filter_works()
    {

        Contact::factory()->fromTelegram()->create(['name' => 'Telegram User']);
        Contact::factory()->fromGmail()->create(['name' => 'Gmail User']);

        Livewire::test(ContactsList::class)
            ->set('selectedSources', ['telegram'])
            ->assertSee('Telegram User')
            ->assertDontSee('Gmail User');
    }

    /** @test */
    public function contacts_list_tag_filter_works()
    {

        Contact::factory()->crypto()->create(['name' => 'Crypto User']);
        Contact::factory()->business()->create(['name' => 'Business User']);

        Livewire::test(ContactsList::class)
            ->set('selectedTags', ['crypto'])
            ->assertSee('Crypto User')
            ->assertDontSee('Business User');
    }

    /** @test */
    public function contacts_list_clear_filters_resets_all()
    {

        Contact::factory()->fromTelegram()->crypto()->create(['name' => 'Test User']);

        Livewire::test(ContactsList::class)
            ->set('search', 'test')
            ->set('selectedSources', ['telegram'])
            ->set('selectedTags', ['crypto'])
            ->call('clearAllFilters')
            ->assertSet('search', '')
            ->assertSet('selectedSources', [])
            ->assertSet('selectedTags', []);
    }

    /** @test */
    public function contacts_list_pagination_works()
    {

        Contact::factory()->count(25)->create();

        Livewire::test(ContactsList::class)
            ->assertSet('contacts.perPage', 20)
            ->call('nextPage')
            ->assertSet('contacts.currentPage', 2);
    }

    /** @test */
    public function contacts_list_handles_empty_results()
    {

        Livewire::test(ContactsList::class)
            ->set('search', 'nonexistent')
            ->assertSee('No contacts found');
    }

    /** @test */
    public function global_search_component_renders_correctly()
    {

        Livewire::test(GlobalSearch::class)
            ->assertViewIs('livewire.contact.global-search')
            ->assertSet('isOpen', false)
            ->assertSet('search', '')
            ->assertSet('results', []);
    }

    /** @test */
    public function global_search_opens_and_closes()
    {

        Livewire::test(GlobalSearch::class)
            ->call('openModal')
            ->assertSet('isOpen', true)
            ->call('closeModal')
            ->assertSet('isOpen', false);
    }

    /** @test */
    public function global_search_performs_search()
    {

        Contact::factory()->create(['name' => 'John Crypto', 'tags' => ['crypto']]);
        Contact::factory()->create(['name' => 'Jane Business', 'tags' => ['business']]);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'crypto')
            ->assertCount('results', 1)
            ->assertSet('results.0.name', 'John Crypto');
    }

    /** @test */
    public function global_search_limits_results()
    {

        Contact::factory()->count(15)->create(['name' => 'Test User']);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'Test')
            ->assertCount('results', 10);
    }

    /** @test */
    public function global_search_keyboard_navigation_works()
    {

        Contact::factory()->create(['name' => 'First Contact']);
        Contact::factory()->create(['name' => 'Second Contact']);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'Contact')
            ->assertSet('selectedIndex', -1)
            ->call('selectNext')
            ->assertSet('selectedIndex', 0)
            ->call('selectNext')
            ->assertSet('selectedIndex', 1)
            ->call('selectPrevious')
            ->assertSet('selectedIndex', 0);
    }

    /** @test */
    public function global_search_keyboard_navigation_wraps_around()
    {

        Contact::factory()->count(3)->create(['name' => 'Contact']);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'Contact')
            ->call('selectNext')
            ->call('selectNext')
            ->call('selectNext')
            ->call('selectNext')
            ->assertSet('selectedIndex', 0);
    }

    /** @test */
    public function global_search_previous_from_first_goes_to_last()
    {

        Contact::factory()->count(3)->create(['name' => 'Contact']);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'Contact')
            ->call('selectNext')
            ->call('selectPrevious')
            ->assertSet('selectedIndex', 2);
    }

    /** @test */
    public function global_search_resets_selection_on_new_search()
    {

        Contact::factory()->create(['name' => 'First Contact']);
        Contact::factory()->create(['name' => 'Second Contact']);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'First')
            ->call('selectNext')
            ->assertSet('selectedIndex', 0)
            ->set('search', 'Second')
            ->assertSet('selectedIndex', -1);
    }

    /** @test */
    public function global_search_handles_empty_search()
    {

        Contact::factory()->count(3)->create();

        Livewire::test(GlobalSearch::class)
            ->set('search', '')
            ->assertSet('results', [])
            ->assertSet('selectedIndex', -1);
    }

    /** @test */
    public function global_search_clears_results_on_close()
    {

        Contact::factory()->create(['name' => 'Test Contact']);

        Livewire::test(GlobalSearch::class)
            ->set('search', 'Test')
            ->assertCount('results', 1)
            ->call('closeModal')
            ->assertSet('search', '')
            ->assertSet('results', [])
            ->assertSet('selectedIndex', -1);
    }

    /** @test */
    public function contacts_list_shows_contact_details()
    {

        $contact = Contact::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'tags' => ['crypto', 'business'],
            'sources' => ['telegram', 'google_oauth'],
        ]);

        Livewire::test(ContactsList::class)
            ->assertSee('John Doe')
            ->assertSee('john@example.com')
            ->assertSee('+1234567890')
            ->assertSee('crypto')
            ->assertSee('business')
            ->assertSee('telegram')
            ->assertSee('google_oauth');
    }

    /** @test */
    public function contacts_list_handles_contacts_without_optional_fields()
    {

        Contact::factory()->create([
            'name' => 'Minimal Contact',
            'email' => null,
            'phone' => null,
            'tags' => null,
            'sources' => ['crm'],
        ]);

        Livewire::test(ContactsList::class)
            ->assertSee('Minimal Contact')
            ->assertSee('crm');
    }

    /** @test */
    public function contacts_list_search_is_case_insensitive()
    {

        Contact::factory()->create(['name' => 'John Doe']);

        Livewire::test(ContactsList::class)
            ->set('search', 'john')
            ->assertSee('John Doe')
            ->set('search', 'JOHN')
            ->assertSee('John Doe')
            ->set('search', 'JoHn')
            ->assertSee('John Doe');
    }

    /** @test */
    public function contacts_list_multiple_source_filter_works()
    {

        Contact::factory()->fromTelegram()->create(['name' => 'Telegram User']);
        Contact::factory()->fromWhatsApp()->create(['name' => 'WhatsApp User']);
        Contact::factory()->fromGmail()->create(['name' => 'Gmail User']);

        Livewire::test(ContactsList::class)
            ->set('selectedSources', ['telegram', 'whatsapp'])
            ->assertSee('Telegram User')
            ->assertSee('WhatsApp User')
            ->assertDontSee('Gmail User');
    }

    /** @test */
    public function contacts_list_multiple_tag_filter_works()
    {

        Contact::factory()->create(['name' => 'Crypto User', 'tags' => ['crypto']]);
        Contact::factory()->create(['name' => 'Business User', 'tags' => ['business']]);
        Contact::factory()->create(['name' => 'Social User', 'tags' => ['social']]);

        Livewire::test(ContactsList::class)
            ->set('selectedTags', ['crypto', 'business'])
            ->assertSee('Crypto User')
            ->assertSee('Business User')
            ->assertDontSee('Social User');
    }
}


