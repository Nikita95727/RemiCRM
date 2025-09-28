<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Models\ContactIntegration;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseOperationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function contact_model_has_correct_fillable_fields()
    {

        $contact = Contact::create([
            'name' => 'Test User',
            'phone' => '+1234567890',
            'email' => 'test@example.com',
            'sources' => ['telegram'],
            'notes' => 'Test notes',
            'tags' => ['crypto'],
        ]);

        $this->assertEquals('Test User', $contact->name);
        $this->assertEquals('+1234567890', $contact->phone);
        $this->assertEquals('test@example.com', $contact->email);
        $this->assertEquals(['telegram'], $contact->sources);
        $this->assertEquals('Test notes', $contact->notes);
        $this->assertEquals(['crypto'], $contact->tags);
    }

    /** @test */
    public function contact_model_casts_sources_and_tags_as_arrays()
    {

        $contact = Contact::create([
            'name' => 'Test User',
            'sources' => ['telegram', 'whatsapp'],
            'tags' => ['crypto', 'business'],
        ]);

        $this->assertIsArray($contact->sources);
        $this->assertIsArray($contact->tags);
        $this->assertEquals(['telegram', 'whatsapp'], $contact->sources);
        $this->assertEquals(['crypto', 'business'], $contact->tags);
    }

    /** @test */
    public function contact_search_scope_works_correctly()
    {

        Contact::factory()->create(['name' => 'John Crypto', 'email' => 'john@crypto.com', 'tags' => ['crypto']]);
        Contact::factory()->create(['name' => 'Jane Business', 'email' => 'jane@business.com', 'tags' => ['business']]);
        Contact::factory()->create(['name' => 'Bob Social', 'phone' => '+1234567890', 'tags' => ['social']]);

        $nameResults = Contact::search('John')->get();
        $this->assertCount(1, $nameResults);
        $this->assertEquals('John Crypto', $nameResults->first()->name);

        $emailResults = Contact::search('business.com')->get();
        $this->assertCount(1, $emailResults);
        $this->assertEquals('Jane Business', $emailResults->first()->name);

        $phoneResults = Contact::search('1234567890')->get();
        $this->assertCount(1, $phoneResults);
        $this->assertEquals('Bob Social', $phoneResults->first()->name);

        $tagResults = Contact::search('crypto')->get();
        $this->assertCount(1, $tagResults);
        $this->assertEquals('John Crypto', $tagResults->first()->name);
    }

    /** @test */
    public function contact_search_is_case_insensitive()
    {

        Contact::factory()->create(['name' => 'John Doe', 'email' => 'JOHN@EXAMPLE.COM']);

        $results1 = Contact::search('john')->get();
        $results2 = Contact::search('JOHN')->get();
        $results3 = Contact::search('example.com')->get();
        $results4 = Contact::search('EXAMPLE.COM')->get();

        $this->assertCount(1, $results1);
        $this->assertCount(1, $results2);
        $this->assertCount(1, $results3);
        $this->assertCount(1, $results4);
    }

    /** @test */
    public function integrated_account_model_has_correct_relationships()
    {

        $user = User::factory()->create();
        $account = IntegratedAccount::factory()->create(['user_id' => $user->id]);

        $account->load('user');

        $this->assertEquals($user->id, $account->user->id);
        $this->assertEquals($user->name, $account->user->name);
    }

    /** @test */
    public function contact_integration_model_creates_correctly()
    {

        $contact = Contact::factory()->create();
        $account = IntegratedAccount::factory()->create();

        $integration = ContactIntegration::create([
            'contact_id' => $contact->id,
            'integrated_account_id' => $account->id,
            'external_id' => 'external-chat-123',
        ]);

        $this->assertDatabaseHas('contact_integrations', [
            'contact_id' => $contact->id,
            'integrated_account_id' => $account->id,
            'external_id' => 'external-chat-123',
        ]);
    }

    /** @test */
    public function contact_has_many_contact_integrations_relationship()
    {

        $contact = Contact::factory()->create();
        $account1 = IntegratedAccount::factory()->telegram()->create();
        $account2 = IntegratedAccount::factory()->whatsapp()->create();

        ContactIntegration::create([
            'contact_id' => $contact->id,
            'integrated_account_id' => $account1->id,
            'external_id' => 'telegram-chat-123',
        ]);

        ContactIntegration::create([
            'contact_id' => $contact->id,
            'integrated_account_id' => $account2->id,
            'external_id' => 'whatsapp-chat-456',
        ]);

        $contact->load('contactIntegrations');

        $this->assertCount(2, $contact->contactIntegrations);
    }

    /** @test */
    public function contact_integration_belongs_to_contact_and_account()
    {

        $contact = Contact::factory()->create(['name' => 'Test Contact']);
        $account = IntegratedAccount::factory()->create(['account_name' => 'Test Account']);

        $integration = ContactIntegration::create([
            'contact_id' => $contact->id,
            'integrated_account_id' => $account->id,
            'external_id' => 'external-123',
        ]);

        $integration->load(['contact', 'integratedAccount']);

        $this->assertEquals('Test Contact', $integration->contact->name);
        $this->assertEquals('Test Account', $integration->integratedAccount->account_name);
    }

    /** @test */
    public function database_handles_large_contact_datasets()
    {

        $contacts = Contact::factory()->count(100)->create();

        $this->assertCount(100, $contacts);
        $this->assertEquals(100, Contact::count());
    }

    /** @test */
    public function database_handles_unicode_characters_correctly()
    {

        $contact = Contact::create([
            'name' => 'Владимир Пупкин',
            'notes' => 'Заметки на русском языке с эмодзи 🚀💎',
            'tags' => ['крипта', 'бизнес'],
        ]);

        $this->assertEquals('Владимир Пупкин', $contact->name);
        $this->assertEquals('Заметки на русском языке с эмодзи 🚀💎', $contact->notes);
        $this->assertEquals(['крипта', 'бизнес'], $contact->tags);
    }

    /** @test */
    public function database_handles_null_values_correctly()
    {

        $contact = Contact::create([
            'name' => 'Minimal Contact',
            'phone' => null,
            'email' => null,
            'notes' => null,
            'tags' => null,
            'sources' => ['crm'],
        ]);

        $this->assertEquals('Minimal Contact', $contact->name);
        $this->assertNull($contact->phone);
        $this->assertNull($contact->email);
        $this->assertNull($contact->notes);
        $this->assertNull($contact->tags);
        $this->assertEquals(['crm'], $contact->sources);
    }

    /** @test */
    public function database_constraints_prevent_duplicate_external_ids()
    {

        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();
        $account = IntegratedAccount::factory()->create();

        ContactIntegration::create([
            'contact_id' => $contact1->id,
            'integrated_account_id' => $account->id,
            'external_id' => 'unique-chat-123',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        ContactIntegration::create([
            'contact_id' => $contact2->id,
            'integrated_account_id' => $account->id,
            'external_id' => 'unique-chat-123',
        ]);
    }

    /** @test */
    public function database_timestamps_are_handled_correctly()
    {

        $beforeCreate = now();

        $contact = Contact::factory()->create();

        $afterCreate = now();

        $this->assertNotNull($contact->created_at);
        $this->assertNotNull($contact->updated_at);
        $this->assertTrue($contact->created_at->between($beforeCreate, $afterCreate));
        $this->assertTrue($contact->updated_at->between($beforeCreate, $afterCreate));
    }

    /** @test */
    public function integrated_account_tracks_sync_timestamp()
    {

        $account = IntegratedAccount::factory()->neverSynced()->create();
        $this->assertNull($account->last_sync_at);

        $account->update(['last_sync_at' => now()]);

        $this->assertNotNull($account->fresh()->last_sync_at);
    }

    /** @test */
    public function contact_factory_states_work_correctly()
    {

        $cryptoContact = Contact::factory()->crypto()->create();
        $this->assertContains('crypto', $cryptoContact->tags);
        $this->assertContains('telegram', $cryptoContact->sources);

        $businessContact = Contact::factory()->business()->create();
        $this->assertContains('business', $businessContact->tags);
        $this->assertContains('gmail', $businessContact->sources);

        $untaggedContact = Contact::factory()->untagged()->create();
        $this->assertNull($untaggedContact->tags);
    }

    /** @test */
    public function integrated_account_factory_states_work_correctly()
    {

        $telegramAccount = IntegratedAccount::factory()->telegram()->create();
        $this->assertEquals('telegram', $telegramAccount->provider);

        $whatsappAccount = IntegratedAccount::factory()->whatsapp()->create();
        $this->assertEquals('whatsapp', $whatsappAccount->provider);

        $gmailAccount = IntegratedAccount::factory()->gmail()->create();
        $this->assertEquals('gmail', $gmailAccount->provider);

        $neverSynced = IntegratedAccount::factory()->neverSynced()->create();
        $this->assertNull($neverSynced->last_sync_at);

        $recentlySynced = IntegratedAccount::factory()->recentlySynced()->create();
        $this->assertNotNull($recentlySynced->last_sync_at);

        $inactive = IntegratedAccount::factory()->inactive()->create();
        $this->assertFalse($inactive->is_active);
    }
}


