<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IntegrationSuccessController;
use App\Http\Controllers\CheckIntegrationController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ContactSearchController;

Route::middleware('guest')->group(function () {
    Route::get('/', fn() => redirect()->route('login'));
});

Route::middleware(['auth', 'App\Http\Middleware\EnsureTwoFactorIsConfirmed'])->group(function () {
    Route::view('/contacts', 'contacts')->name('contacts');
    Route::get('/contacts/search', [ContactSearchController::class, 'search'])->name('contacts.search');
    Route::view('/integration/waiting', 'integration-waiting')->name('integration.waiting');
    Route::view('/telegram/connect', 'telegram-connect')->name('telegram.connect.form');
    Route::view('/profile', 'profile')->name('profile');

    Route::get('/dashboard', fn() => redirect()->route('contacts'))->name('dashboard');

    Route::prefix('integration')->name('integration.')->group(function () {
        Route::get('/success', [IntegrationSuccessController::class, 'show'])->name('success');
        Route::post('/check-status', [CheckIntegrationController::class, 'check'])->name('check-status');
    });

    Route::post('/telegram/connect', [TelegramController::class, 'connect'])->name('telegram.connect');
});

// 2FA management route (without 2FA middleware to allow setup)
Route::get('/two-factor', \App\Livewire\TwoFactor\EnableTwoFactor::class)
    ->middleware(['auth'])
    ->name('two-factor.index');

require __DIR__.'/auth.php';
