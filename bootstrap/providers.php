<?php

return [
    App\Core\Providers\ModuleServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    App\Modules\Contact\Providers\ContactServiceProvider::class,
    App\Modules\Integration\Providers\IntegrationServiceProvider::class,
];
