<?php

declare(strict_types=1);

namespace App\Modules\Integration\Providers;

use App\Modules\Contact\Contracts\ContactRepositoryInterface;
use App\Modules\Contact\Repositories\ContactRepository;
use App\Modules\Integration\Contracts\AccountSyncServiceInterface;
use App\Modules\Integration\Contracts\ContactSyncServiceInterface;
use App\Modules\Integration\Contracts\IntegratedAccountRepositoryInterface;
use App\Modules\Integration\Contracts\IntegrationServiceInterface;
use App\Modules\Integration\Livewire\ConnectAccount;
use App\Modules\Integration\Repositories\IntegratedAccountRepository;
use App\Modules\Integration\Services\AccountSyncService;
use App\Modules\Integration\Services\ContactSyncService;
use App\Modules\Integration\Services\IntegrationService;
use App\Modules\Integration\Services\UnipileService;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class IntegrationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register UnipileService as singleton
        $this->app->singleton(UnipileService::class, function ($app) {
            return new UnipileService(
                config('services.unipile.dsn'),
                config('services.unipile.token'),
                config('services.unipile.base_url')
            );
        });

        // Register repository implementations
        $this->app->bind(IntegratedAccountRepositoryInterface::class, IntegratedAccountRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);

        // Register service implementations
        $this->app->bind(AccountSyncServiceInterface::class, AccountSyncService::class);
        $this->app->bind(ContactSyncServiceInterface::class, ContactSyncService::class);
        $this->app->bind(IntegrationServiceInterface::class, IntegrationService::class);
    }

    public function boot(): void
    {
        // Register Livewire components
        Livewire::component('integration.connect-account', ConnectAccount::class);
    }
}
