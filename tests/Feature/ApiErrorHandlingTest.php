<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Integration\Jobs\SyncContactsFromAccount;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ApiErrorHandlingTest extends TestCase
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
    public function handles_401_unauthorized_errors_gracefully()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'error' => 'Unauthorized',
                'message' => 'Invalid or expired token',
            ], 401),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function handles_404_not_found_errors_gracefully()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'error' => 'Not Found',
                'message' => 'Account not found',
            ], 404),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function handles_500_server_errors_gracefully()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'error' => 'Internal Server Error',
                'message' => 'Something went wrong',
            ], 500),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function handles_network_timeout_errors()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            },
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function handles_malformed_json_responses()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response('invalid-json-response', 200),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function handles_empty_response_bodies()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response('', 200),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function handles_rate_limiting_errors()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded',
            ], 429),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }

    /** @test */
    public function logs_api_errors_for_debugging()
    {

        Log::spy();
        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'error' => 'Unauthorized',
            ], 401),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        Log::shouldHaveReceived('error')
            ->atLeast()
            ->once();
    }

    /** @test */
    public function handles_partial_api_failures()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'chat-1',
                        'name' => 'Working Chat',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                    [
                        'id' => 'chat-2',
                        'name' => 'Broken Chat',
                        'type' => 1,
                        'attendee_provider_id' => '0987654321',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*chat-1*' => Http::response([
                'messages' => [
                    ['text' => 'Hello from working chat', 'sender_id' => '1234567890'],
                ],
            ], 200),
            '*/api/v1/messages*chat-2*' => Http::response([
                'error' => 'Chat not accessible',
            ], 404),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);

        $this->assertDatabaseHas('contacts', ['name' => 'Working Chat']);
    }

    /** @test */
    public function handles_invalid_account_connection()
    {

        $response = $this->post('/integration/connect', [
            'provider' => 'telegram',
            'dsn' => 'invalid.unipile.com:99999',
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function handles_missing_required_fields_in_api_response()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [

                        'name' => 'Incomplete Chat',
                        'type' => 1,
                    ],
                    [
                        'id' => 'complete-chat',
                        'name' => 'Complete Chat',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $this->assertDatabaseHas('contacts', ['name' => 'Complete Chat']);
        $this->assertDatabaseMissing('contacts', ['name' => 'Incomplete Chat']);
    }

    /** @test */
    public function handles_authentication_errors_during_sync()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/accounts/*' => Http::response([
                'error' => 'Authentication required',
                'message' => '2FA code needed',
            ], 401),
        ]);

        $response = $this->post('/integration/check-status');

        $response->assertStatus(200);
        $response->assertJson(['status' => 'error']);
    }

    /** @test */
    public function handles_concurrent_api_requests_gracefully()
    {

        Queue::fake();
        $accounts = IntegratedAccount::factory()->count(3)->neverSynced()->create([
            'user_id' => $this->user->id,
        ]);

        Http::fake([
            '*/api/v1/accounts/*' => Http::response([
                'status' => 'connected',
            ], 200),
        ]);

        $response = $this->post('/integration/check-status');

        $response->assertStatus(200);
        Queue::assertPushed(SyncContactsFromAccount::class, 3);
    }

    /** @test */
    public function handles_api_response_with_missing_data_fields()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'chat-1',
                        'name' => 'Chat with Missing Data',
                        'type' => 1,

                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $this->assertDatabaseHas('contacts', ['name' => 'Chat with Missing Data']);
    }

    /** @test */
    public function handles_message_api_returning_different_structures()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'chat-1',
                        'name' => 'Test Chat',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),

            '*/api/v1/messages*' => Http::sequence()
                ->push(Http::response(['items' => []], 200))
                ->push(Http::response(['messages' => []], 200))
                ->push(Http::response([['object' => 'Message', 'text' => 'Direct array']], 200)),
        ]);

        $this->expectNotToPerformAssertions();
        SyncContactsFromAccount::dispatchSync($account);
    }
}


