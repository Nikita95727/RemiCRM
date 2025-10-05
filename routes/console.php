<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automatic contact synchronization
Schedule::command('contacts:sync-all')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/sync.log'));

// 🧪 TEST: Run sync 3 hours from now (remove after testing)
$testTime = now()->addHours(3)->format('H:i');
Schedule::command('contacts:sync-all --force')
    ->dailyAt($testTime)
    ->name('test-sync-3-hours')
    ->onSuccess(function () {
        \Illuminate\Support\Facades\Log::info('🎉 TEST SYNC COMPLETED AT ' . now()->toDateTimeString());
    })
    ->onFailure(function () {
        \Illuminate\Support\Facades\Log::error('❌ TEST SYNC FAILED AT ' . now()->toDateTimeString());
    });

// Schedule log cleanup
Schedule::command('log:clear')
    ->weekly()
    ->sundays()
    ->at('02:00');