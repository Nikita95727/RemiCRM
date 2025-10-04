<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartQueueWorker extends Command
{
    protected $signature = 'queue:start';

    protected $description = 'Start queue worker in background for development';

    public function handle(): int
    {
        $this->info('🚀 Starting queue worker in background...');

        // Запускаем queue worker в фоне
        $command = 'php artisan queue:work --tries=3 --timeout=60 > /dev/null 2>&1 &';
        $output = shell_exec($command);

        $this->info('✅ Queue worker started in background');
        $this->info('💡 To stop: pkill -f "queue:work"');
        $this->info('💡 To monitor: tail -f storage/logs/laravel.log');

        return 0;
    }
}
