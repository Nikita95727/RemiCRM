<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Contact\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactManagementTest extends TestCase
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
    public function user_can_view_contacts_list()
    {

        Contact::factory()->count(5)->create();

        $response = $this->get('/contacts');

        $response->assertStatus(200);
        $response->assertViewIs('livewire.contact.contacts-list');
    }

    /** @test */
    public function contacts_list_displays_correct_data()
    {

        $contact1 = Contact::factory()->crypto()->create(['name' => 'John Crypto']);
        $contact2 = Contact::factory()->business()->create(['name' => 'Jane Business']);

        $response = $this->get('/contacts');

        $response->assertSee('John Crypto');
        $response->assertSee('Jane Business');
        $response->assertSee('crypto');
        $response->assertSee('business');
    }

    /** @test */
    public function user_can_search_contacts_by_name()
    {

        Contact::factory()->create(['name' => 'John Doe']);
        Contact::factory()->create(['name' => 'Jane Smith']);

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    /** @test */
    public function user_can_search_contacts_by_email()
    {

        Contact::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        Contact::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->set('search', 'john@example.com')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    /** @test */
    public function user_can_search_contacts_by_phone()
    {

        Contact::factory()->create([
            'name' => 'John Doe',
            'phone' => '+1234567890',
        ]);
        Contact::factory()->create([
            'name' => 'Jane Smith',
            'phone' => '+9876543210',
        ]);

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->set('search', '+1234567890')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith');
    }

    /** @test */
    public function user_can_search_contacts_by_tags()
    {

        Contact::factory()->create([
            'name' => 'John Crypto',
            'tags' => ['crypto', 'trading'],
        ]);
        Contact::factory()->create([
            'name' => 'Jane Business',
            'tags' => ['business', 'consulting'],
        ]);

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->set('search', 'crypto')
            ->assertSee('John Crypto')
            ->assertDontSee('Jane Business');
    }

    /** @test */
    public function user_can_filter_contacts_by_source()
    {

        Contact::factory()->fromTelegram()->create(['name' => 'Telegram User']);
        Contact::factory()->fromGmail()->create(['name' => 'Gmail User']);

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->set('selectedSources', ['telegram'])
            ->assertSee('Telegram User')
            ->assertDontSee('Gmail User');
    }

    /** @test */
    public function user_can_filter_contacts_by_multiple_sources()
    {

        Contact::factory()->fromTelegram()->create(['name' => 'Telegram User']);
        Contact::factory()->fromWhatsApp()->create(['name' => 'WhatsApp User']);
        Contact::factory()->fromGmail()->create(['name' => 'Gmail User']);

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->set('selectedSources', ['telegram', 'whatsapp'])
            ->assertSee('Telegram User')
            ->assertSee('WhatsApp User')
            ->assertDontSee('Gmail User');
    }

    /** @test */
    public function user_can_filter_contacts_by_tags()
    {

        Contact::factory()->crypto()->create(['name' => 'Crypto User']);
        Contact::factory()->business()->create(['name' => 'Business User']);

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->set('selectedTags', ['crypto'])
            ->assertSee('Crypto User')
            ->assertDontSee('Business User');
    }

    /** @test */
    public function contacts_list_pagination_works()
    {

        Contact::factory()->count(25)->create();

        Livewire::test(\App\Modules\Contact\Livewire\ContactsList::class)
            ->assertSet('contacts.perPage', 20)
            ->assertSee('Next');
    }

    /** @test */
    public function user_can_view_individual_contact()
    {

        $contact = Contact::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '+1234567890',
            'tags' => ['crypto', 'trading'],
            'notes' => 'Important crypto trader',
        ]);

        $response = $this->get("/contacts/{$contact->id}");

        $response->assertStatus(200);
        $response->assertSee('John Doe');
        $response->assertSee('john@example.com');
        $response->assertSee('+1234567890');
        $response->assertSee('crypto');
        $response->assertSee('trading');
        $response->assertSee('Important crypto trader');
    }

    /** @test */
    public function user_cannot_view_nonexistent_contact()
    {

        $response = $this->get('/contacts/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function global_search_works_with_command_k()
    {

        Contact::factory()->create([
            'name' => 'John Crypto',
            'tags' => ['crypto'],
        ]);
        Contact::factory()->create([
            'name' => 'Jane Business',
            'tags' => ['business'],
        ]);

        Livewire::test(\App\Modules\Contact\Livewire\GlobalSearch::class)
            ->set('search', 'crypto')
            ->assertSet('results.0.name', 'John Crypto')
            ->assertCount('results', 1);
    }

    /** @test */
    public function global_search_returns_empty_for_no_matches()
    {

        Contact::factory()->create(['name' => 'John Doe']);

        Livewire::test(\App\Modules\Contact\Livewire\GlobalSearch::class)
            ->set('search', 'nonexistent')
            ->assertCount('results', 0);
    }

    /** @test */
    public function global_search_limits_results()
    {

        Contact::factory()->count(15)->create([
            'name' => 'Test User',
            'tags' => ['test'],
        ]);

        Livewire::test(\App\Modules\Contact\Livewire\GlobalSearch::class)
            ->set('search', 'test')
            ->assertCount('results', 10);
    }
}


