<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Modules\Integration\Services\UnipileService;
use Illuminate\Console\Command;

class TestUnipileConnection extends Command
{
    protected $signature = 'unipile:test';

    protected $description = 'Test Unipile API connection and configuration';

    public function handle(): int
    {
        $this->info('Testing Unipile API Configuration...');

        $dsn = config('services.unipile.dsn');
        $token = config('services.unipile.token');
        $baseUrl = "https://{$dsn}/api/v1"; // Construct base URL from DSN

        $this->info('DSN: '.($dsn ? 'Set' : 'Not set'));
        $this->info('Token: '.($token ? 'Set' : 'Not set'));
        $this->info('Base URL: '.$baseUrl);

        if (! $dsn || ! $token) {
            $this->error('Unipile DSN and Token must be set in .env file');
            $this->info('Add these lines to your .env:');
            $this->info('UNIPILE_DSN=your_dsn_here');
            $this->info('UNIPILE_TOKEN=your_token_here');

            return 1;
        }

        $this->info('Testing API connection...');

        try {
            $unipileService = app(UnipileService::class);
            $accounts = $unipileService->listAccounts();

            $this->info('✅ Connection successful!');
            $this->info('Found '.count($accounts).' accounts');

            if (! empty($accounts)) {
                $this->info('Raw account data:');
                $this->info(json_encode($accounts, JSON_PRETTY_PRINT));

                // Parse the nested structure
                $items = $accounts['items'] ?? [];

                if (! empty($items)) {
                    $tableData = [];
                    foreach ($items as $account) {
                        $status = 'UNKNOWN';
                        if (! empty($account['sources'])) {
                            $status = $account['sources'][0]['status'] ?? 'UNKNOWN';
                        }

                        $tableData[] = [
                            $account['id'] ?? 'N/A',
                            $account['type'] ?? 'N/A',
                            $account['name'] ?? 'N/A',
                            $status,
                            $account['created_at'] ?? 'N/A',
                        ];
                    }
                    
                    $this->table(
                        ['ID', 'Provider', 'Name', 'Status', 'Created'],
                        $tableData
                    );
                }
            }

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Connection failed: '.$e->getMessage());

            return 1;
        }
    }
}
