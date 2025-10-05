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

            if (File::exists($providerPath)) {
                $providerClass = "App\\Modules\\{$moduleName}\\Providers\\{$moduleName}ServiceProvider";

                if (class_exists($providerClass)) {
                    $this->app->register($providerClass);
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
