<?php

declare(strict_types=1);

namespace App\Modules\Contact\Providers;

use App\Modules\Contact\Services\ContactRepository;
use App\Modules\Contact\Services\ContactService;
use App\Shared\Contracts\ContactRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class ContactServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerServices();
        $this->registerRepositories();
    }

    public function boot(): void
    {
        $this->registerLivewireComponents();
    }

    private function registerLivewireComponents(): void
    {
        \Livewire\Livewire::component('contact.contacts-list', \App\Modules\Contact\Livewire\ContactsList::class);
        \Livewire\Livewire::component('contact.global-search', \App\Modules\Contact\Livewire\GlobalSearch::class);
        \Livewire\Livewire::component('contact.contact-form', \App\Modules\Contact\Livewire\ContactForm::class);
    }

    private function registerServices(): void
    {
        $this->app->singleton(ContactService::class, function ($app) {
            return new ContactService(
                $app->make(ContactRepositoryInterface::class)
            );
        });
    }

    private function registerRepositories(): void
    {
        $this->app->bind(
            ContactRepositoryInterface::class,
            \App\Modules\Contact\Repositories\ContactRepository::class
        );
    }

    /**
     * @return array<string>
     */
    public function provides(): array
    {
        return [
            ContactService::class,
            ContactRepositoryInterface::class,
        ];
    }
}
