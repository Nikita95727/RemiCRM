<?php

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Contact\Models\Contact;
use App\Modules\Integration\Jobs\SyncContactsFromAccount;
use App\Modules\Integration\Models\IntegratedAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AutoTaggingTest extends TestCase
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
    public function auto_tagging_assigns_crypto_tag_for_crypto_conversations()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'crypto-chat-1',
                        'name' => 'Bitcoin Trader',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [
                    [
                        'text' => 'I want to buy Bitcoin and Ethereum',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->toISOString(),
                    ],
                    [
                        'text' => 'What is the current price of BTC?',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(5)->toISOString(),
                    ],
                    [
                        'text' => 'Can you help me with cryptocurrency trading?',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(10)->toISOString(),
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $contact = Contact::where('name', 'Bitcoin Trader')->first();
        $this->assertNotNull($contact);
        $this->assertContains('crypto', $contact->tags ?? []);
    }

    /** @test */
    public function auto_tagging_assigns_business_tag_for_business_conversations()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'business-chat-1',
                        'name' => 'Corporate Client',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [
                    [
                        'text' => 'We need to discuss the quarterly business report',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->toISOString(),
                    ],
                    [
                        'text' => 'Our company is looking for investment opportunities',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(5)->toISOString(),
                    ],
                    [
                        'text' => 'Let\'s schedule a meeting to discuss the proposal',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(10)->toISOString(),
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $contact = Contact::where('name', 'Corporate Client')->first();
        $this->assertNotNull($contact);
        $this->assertContains('business', $contact->tags ?? []);
    }

    /** @test */
    public function auto_tagging_assigns_bot_tag_for_bot_conversations()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'bot-chat-1',
                        'name' => 'Support Bot',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [
                    [
                        'text' => 'Bot are allowed here',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->toISOString(),
                    ],
                    [
                        'text' => 'This is an automated message',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(5)->toISOString(),
                    ],
                    [
                        'text' => 'Bot response: Command executed successfully',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(10)->toISOString(),
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $contact = Contact::where('name', 'Support Bot')->first();
        $this->assertNotNull($contact);
        $this->assertContains('bot', $contact->tags ?? []);
    }

    /** @test */
    public function auto_tagging_handles_empty_messages_gracefully()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'empty-chat-1',
                        'name' => 'Empty Chat',
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

        $contact = Contact::where('name', 'Empty Chat')->first();
        $this->assertNotNull($contact);
        $this->assertEmpty($contact->tags ?? []);
    }

    /** @test */
    public function auto_tagging_handles_api_message_errors()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'error-chat-1',
                        'name' => 'Error Chat',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([], 404),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $contact = Contact::where('name', 'Error Chat')->first();
        $this->assertNotNull($contact);
        $this->assertEmpty($contact->tags ?? []);
    }

    /** @test */
    public function auto_tagging_processes_mixed_content_conversations()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'mixed-chat-1',
                        'name' => 'Mixed Content',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [
                    [
                        'text' => 'Hello, how are you?',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->toISOString(),
                    ],
                    [
                        'text' => 'What do you think about Bitcoin?',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(5)->toISOString(),
                    ],
                    [
                        'text' => 'I heard crypto is volatile',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(10)->toISOString(),
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $contact = Contact::where('name', 'Mixed Content')->first();
        $this->assertNotNull($contact);

        $this->assertContains('crypto', $contact->tags ?? []);
    }

    /** @test */
    public function auto_tagging_assigns_social_tag_for_casual_conversations()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'social-chat-1',
                        'name' => 'Friend',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [
                    [
                        'text' => 'Hey, want to hang out tonight?',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->toISOString(),
                    ],
                    [
                        'text' => 'How was your weekend?',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(5)->toISOString(),
                    ],
                    [
                        'text' => 'Let\'s grab coffee sometime',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(10)->toISOString(),
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $contact = Contact::where('name', 'Friend')->first();
        $this->assertNotNull($contact);
        $this->assertContains('social', $contact->tags ?? []);
    }

    /** @test */
    public function auto_tagging_limits_to_single_most_relevant_tag()
    {

        $account = IntegratedAccount::factory()->telegram()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'single-tag-chat',
                        'name' => 'Crypto Business',
                        'type' => 1,
                        'attendee_provider_id' => '1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [
                    [
                        'text' => 'Bitcoin trading business opportunity',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->toISOString(),
                    ],
                    [
                        'text' => 'Cryptocurrency investment company',
                        'sender_id' => '1234567890',
                        'timestamp' => now()->subMinutes(5)->toISOString(),
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($account);

        $contact = Contact::where('name', 'Crypto Business')->first();
        $this->assertNotNull($contact);
        $this->assertCount(1, $contact->tags ?? []);
        $this->assertTrue(
            in_array('crypto', $contact->tags ?? []) ||
            in_array('business', $contact->tags ?? [])
        );
    }

    /** @test */
    public function auto_tagging_works_for_different_providers()
    {

        $whatsappAccount = IntegratedAccount::factory()->whatsapp()->create();

        Http::fake([
            '*/api/v1/chats*' => Http::response([
                'items' => [
                    [
                        'id' => 'whatsapp-crypto-chat',
                        'name' => 'WhatsApp Crypto User',
                        'type' => 1,
                        'attendee_provider_id' => '+1234567890',
                    ],
                ],
            ], 200),
            '*/api/v1/messages*' => Http::response([
                'messages' => [
                    [
                        'text' => 'I want to invest in Bitcoin',
                        'sender_id' => '+1234567890',
                        'timestamp' => now()->toISOString(),
                    ],
                ],
            ], 200),
        ]);

        SyncContactsFromAccount::dispatchSync($whatsappAccount);

        $contact = Contact::where('name', 'WhatsApp Crypto User')->first();
        $this->assertNotNull($contact);
        $this->assertContains('crypto', $contact->tags ?? []);
        $this->assertContains('whatsapp', $contact->sources ?? []);
    }

    /** @test */
    public function search_works_with_auto_assigned_tags()
    {

        Contact::factory()->create([
            'name' => 'Crypto Trader',
            'tags' => ['crypto'],
        ]);
        Contact::factory()->create([
            'name' => 'Business Partner',
            'tags' => ['business'],
        ]);

        $response = $this->get('/contacts?search=crypto');

        $response->assertSee('Crypto Trader');
        $response->assertDontSee('Business Partner');
    }
}


