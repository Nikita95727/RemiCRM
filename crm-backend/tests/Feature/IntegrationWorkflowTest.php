<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Jobs\SyncContactsFromAccount;
use App\Modules\Integration\Models\ContactIntegration;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class IntegrationWorkflowTest extends TestCase
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
    public function user_can_view_integration_page()
    {

        $response = $this->get('/integration');

        $response->assertStatus(200);
        $response->assertSee('Connect Account');
    }

    /** @test */
    public function user_can_connect_telegram_account()
    {

        Http::fake([
            '*/api/v1/accounts' => Http::response([
                'id' => 'test-account-id',
                'name' => '380660642582',
                'provider' => 'telegram',
            ], 200),
        ]);

        $response = $this->post('/integration/connect', [
            'provider' => 'telegram',
            'dsn' => 'api17.unipile.com:14705',
        ]);

        $response->assertRedirect('/integration/waiting');
        $this->assertDatabaseHas('integrated_accounts', [
            'user_id' => $this->user->id,
            'provider' => 'telegram',
            'unipile_account_id' => 'test-account-id',
        ]);
    }

    /** @test */
    public function integration_status_check_triggers_sync_for_new_accounts()
    {

        Queue::fake();

        $account = IntegratedAccount::factory()->neverSynced()->create([
            'user_id' => $this->user->id,
        ]);

        Http::fake([
            '*/api/v1/accounts/*' => Http::response([
                'id' => $account->unipile_account_id,
                'status' => 'connected',
            ], 200),
        ]);

        $response = $this->post('/integration/check-status');

        $response->assertStatus(200);
        Queue::assertPushed(SyncContactsFromAccount::class);
    }

    /** @test */
    public function sync_job_creates_contacts_from_api_response()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'chat-1',
                        'name' => 'John Doe',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                    [
                        'id' => 'chat-2',
                        'name' => 'Jane Smith',
                        'type' => 1,
                        'attendee_provider_id' => '0987654321',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $this->assertDatabaseHas('contacts', ['name' => 'John Doe']);
        $this->assertDatabaseHas('contacts', ['name' => 'Jane Smith']);
        $this->assertDatabaseHas('contact_integrations', [
            'external_id' => 'chat-1',
            'integrated_account_id' => $account->id,
        ]);
    }

    /** @test */
    public function sync_job_handles_api_errors_gracefully()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([], 401),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function sync_job_skips_existing_contacts()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        $existingContact = Contact::factory()->create(['name' => 'John Doe']);
        ContactIntegration::create([
            'contact_id' => $existingContact->id,
            'integrated_account_id' => $account->id,
            'external_id' => 'chat-1',
        ]);

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'chat-1',
                        'name' => 'John Doe',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $this->assertEquals(1, Contact::where('name', 'John Doe')->count());
    }

    /** @test */
    public function user_can_disconnect_account()
    {

        $account = IntegratedAccount::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->delete("/integration/accounts/{$account->id}");

        $response->assertRedirect('/integration');
        $this->assertDatabaseMissing('integrated_accounts', [
            'id' => $account->id,
        ]);
    }

    /** @test */
    public function user_cannot_disconnect_other_users_accounts()
    {

        $otherUser = User::factory()->create();
        $account = IntegratedAccount::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->delete("/integration/accounts/{$account->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('integrated_accounts', [
            'id' => $account->id,
        ]);
    }

    /** @test */
    public function sync_updates_last_sync_timestamp()
    {

        $account = IntegratedAccount::factory()->neverSynced()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response(['items' => []], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $account->refresh();
        $this->assertNotNull($account->last_sync_at);
    }

    /** @test */
    public function integration_handles_different_providers()
    {

        Http::fake();

        $providers = ['telegram', 'whatsapp', 'gmail'];

        foreach ($providers as $provider) {

            $response = $this->post('/integration/connect', [
                'provider' => $provider,
                'dsn' => 'api17.unipile.com:14705',
            ]);

            $response->assertRedirect('/integration/waiting');
        }

        foreach ($providers as $provider) {
            $this->assertDatabaseHas('integrated_accounts', [
                'user_id' => $this->user->id,
                'provider' => $provider,
            ]);
        }
    }

    /** @test */
    public function waiting_page_shows_connection_status()
    {

        IntegratedAccount::factory()->neverSynced()->create([
            'user_id' => $this->user->id,
            'provider' => 'telegram',
        ]);

        $response = $this->get('/integration/waiting');

        $response->assertStatus(200);
        $response->assertSee('Connecting');
        $response->assertSee('telegram');
    }

    /** @test */
    public function integration_redirects_to_contacts_when_complete()
    {

        IntegratedAccount::factory()->recentlySynced()->create([
            'user_id' => $this->user->id,
        ]);

        Http::fake([
            '*/api/v1/accounts/*' => Http::response([
                'status' => 'connected',
            ], 200),
        ]);

        $response = $this->post('/integration/check-status');

        $response->assertJson(['redirect' => '/contacts']);
    }
}


