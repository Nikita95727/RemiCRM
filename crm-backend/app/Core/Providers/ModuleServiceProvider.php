<?php

declare(strict_types=1);

namespace App\Core\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerModuleProviders();
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
        $this->loadModuleMigrations();
        $this->loadModuleViews();
    }

    private function registerModuleProviders(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $providerPath = $modulePath.'/Providers/'.$moduleName.'ServiceProvider.php';

            \Log::info("ModuleServiceProvider: Checking module {$moduleName}", [
                'module_path' => $modulePath,
                'provider_path' => $providerPath,
                'provider_exists' => File::exists($providerPath),
            ]);

            if (File::exists($providerPath)) {
                $providerClass = "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";

                \Log::info('ModuleServiceProvider: Registering provider', [
                    'provider_class' => $providerClass,
                    'class_exists' => class_exists($providerClass),
                ]);

                if (class_exists($providerClass)) {
                    $this->app->register($providerClass);
                    \Log::info("ModuleServiceProvider: Registered {$providerClass}");
                }
            }
        }
    }

    private function loadModuleRoutes(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = basename($modulePath);
            $routesPath = $modulePath.'/Routes';

            $webRoutesPath = $routesPath.'/web.php';
            if (File::exists($webRoutesPath)) {
                $this->loadRoutesFrom($webRoutesPath);
            }

            $apiRoutesPath = $routesPath.'/api.php';
            if (File::exists($apiRoutesPath)) {
                $this->loadRoutesFrom($apiRoutesPath);
            }
        }
    }

    private function loadModuleMigrations(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $migrationsPath = $modulePath.'/Database/Migrations';

            if (File::exists($migrationsPath)) {
                $this->loadMigrationsFrom($migrationsPath);
            }
        }
    }

    private function loadModuleViews(): void
    {
        $modulesPath = app_path('Modules');

        if (! File::exists($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);

        foreach ($modules as $modulePath) {
            $moduleName = strtolower(basename($modulePath));
            $viewsPath = $modulePath.'/Resources/Views';

            if (File::exists($viewsPath)) {
                $this->loadViewsFrom($viewsPath, $moduleName);
            }
        }
    }
}
