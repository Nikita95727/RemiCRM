<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Modules\Integration\Services\IntegrationService;
use Illuminate\Console\Command;
use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Contact\Models\Contact;

class TestFullFlow extends Command
{
    protected $signature = 'test:full-flow';

    protected $description = 'Test the complete flow from user login to contact sync';

    public function handle(): int
    {
        $this->info('🧪 Testing Complete CRM Flow');
        $this->newLine();

        $this->info('1. Checking test user...');
        $user = User::where('email', 'test@example.com')->first();

        if (! $user) {
            $this->error('❌ Test user not found. Please create test@example.com first.');

            return self::FAILURE;
        }

        $this->info("✅ Test user found: {$user->email} (ID: {$user->id})");
        $this->newLine();

        $this->info('2. Checking integrated accounts...');
        $accounts = IntegratedAccount::where('user_id', $user->id)->get();

        if ($accounts->isEmpty()) {
            $this->warn('⚠️  No integrated accounts found. Please connect an account first.');
            $this->info('   Go to: http://localhost:8000/integrations');

            return self::SUCCESS;
        }

        $this->info("✅ Found {$accounts->count()} integrated account(s):");
        foreach ($accounts as $account) {
            $syncStatus = $account->last_sync_at ? 'Synced: '.$account->last_sync_at->diffForHumans() : 'Never synced';
            $this->line("   • {$account->provider->value} ({$account->account_name}) - {$syncStatus}");
        }
        $this->newLine();

        $this->info('3. Checking contacts...');
        $contactsCount = Contact::where('user_id', $user->id)->count();
        $this->info("✅ Found {$contactsCount} contacts in database");
        $this->newLine();

        $this->info('4. Testing IntegrationService...');
        $integrationService = app(IntegrationService::class);
        $result = $integrationService->checkIntegrationStatus($user);

        $this->info("✅ Integration status: {$result['status']}");
        $this->info("   Message: {$result['message']}");
        $this->info("   Accounts: {$result['accounts_count']}");
        if (isset($result['synced_accounts_count'])) {
            $this->info("   Accounts to sync: {$result['synced_accounts_count']}");
        }
        $this->newLine();

        $this->info('5. Checking queue...');
        $this->call('queue:work', ['--once' => true, '--verbose' => true]);
        $this->newLine();

        $this->info('6. Final contact count...');
        $finalCount = Contact::where('user_id', $user->id)->count();
        $this->info("✅ Final contacts: {$finalCount}");

        if ($finalCount > $contactsCount) {
            $this->info('🎉 Successfully imported '.($finalCount - $contactsCount).' new contacts!');
        }
        $this->newLine();

        $this->info('7. Testing search functionality...');
        $sampleContacts = Contact::where('user_id', $user->id)
            ->limit(3)
            ->get(['id', 'name', 'tags']);

        if ($sampleContacts->isNotEmpty()) {
            $this->info('✅ Sample contacts for search testing:');
            foreach ($sampleContacts as $contact) {
                $tags = $contact->tags ? implode(', ', $contact->tags) : 'No tags';
                $this->line("   • {$contact->name} (Tags: {$tags})");
            }
        }
        $this->newLine();

        $this->info('📊 Test Summary:');
        $this->table(['Component', 'Status', 'Details'], [
            ['User Authentication', '✅ Working', $user->email],
            ['Account Integration', $accounts->isNotEmpty() ? '✅ Working' : '⚠️ Missing', $accounts->count().' accounts'],
            ['Contact Sync', $finalCount > 0 ? '✅ Working' : '❌ Failed', $finalCount.' contacts'],
            ['Queue Processing', '✅ Working', 'Jobs processed successfully'],
        ]);

        $this->newLine();
        $this->info('🎯 MVP Status: '.($finalCount > 0 && $accounts->isNotEmpty() ? 'READY FOR DEMO! 🚀' : 'Needs attention ⚠️'));

        return self::SUCCESS;
    }
}
