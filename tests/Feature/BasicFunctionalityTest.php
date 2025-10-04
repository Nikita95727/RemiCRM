<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicFunctionalityTest extends TestCase
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
    public function authenticated_user_can_access_contacts_page()
    {
        $response = $this->get('/contacts');

        $response->assertStatus(200);
    }

    /** @test */
    public function contact_can_be_created_with_required_fields()
    {
        $contact = Contact::create([
            'user_id' => $this->user->id,
            'name' => 'Test Contact',
            'sources' => ['crm'],
        ]);

        $this->assertDatabaseHas('contacts', [
            'name' => 'Test Contact',
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function contact_search_works()
    {
        Contact::create([
            'user_id' => $this->user->id,
            'name' => 'John Crypto',
            'sources' => ['telegram'],
            'tags' => ['crypto'],
        ]);

        Contact::create([
            'user_id' => $this->user->id,
            'name' => 'Jane Business',
            'sources' => ['google_oauth'],
            'tags' => ['business'],
        ]);

        $cryptoResults = Contact::search('crypto')->get();
        $businessResults = Contact::search('business')->get();

        $this->assertCount(1, $cryptoResults);
        $this->assertCount(1, $businessResults);
        $this->assertEquals('John Crypto', $cryptoResults->first()->name);
        $this->assertEquals('Jane Business', $businessResults->first()->name);
    }

    /** @test */
    public function integrated_account_can_be_created()
    {
        $account = IntegratedAccount::create([
            'user_id' => $this->user->id,
            'provider' => 'telegram',
            'account_name' => '380660642582',
            'unipile_account_id' => 'test-account-123',
            'access_token' => 'test-token',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('integrated_accounts', [
            'user_id' => $this->user->id,
            'provider' => 'telegram',
        ]);
    }

    /** @test */
    public function contact_tags_are_stored_as_json()
    {
        $contact = Contact::create([
            'user_id' => $this->user->id,
            'name' => 'Tagged Contact',
            'sources' => ['telegram'],
            'tags' => ['crypto', 'business'],
        ]);

        $this->assertIsArray($contact->tags);
        $this->assertEquals(['crypto', 'business'], $contact->tags);
    }

    /** @test */
    public function contact_sources_are_stored_as_json()
    {
        $contact = Contact::create([
            'user_id' => $this->user->id,
            'name' => 'Multi-Source Contact',
            'sources' => ['telegram', 'whatsapp', 'google_oauth'],
        ]);

        $this->assertIsArray($contact->sources);
        $this->assertEquals(['telegram', 'whatsapp', 'google_oauth'], $contact->sources);
    }

    /** @test */
    public function contact_can_have_optional_fields()
    {
        $contact = Contact::create([
            'user_id' => $this->user->id,
            'name' => 'Complete Contact',
            'sources' => ['telegram'],
            'email' => 'test@example.com',
            'phone' => '+1234567890',
            'notes' => 'Test notes',
            'tags' => ['test'],
        ]);

        $this->assertEquals('test@example.com', $contact->email);
        $this->assertEquals('+1234567890', $contact->phone);
        $this->assertEquals('Test notes', $contact->notes);
        $this->assertEquals(['test'], $contact->tags);
    }
}


