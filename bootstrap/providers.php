<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\PagosServiceProvider;

return [
    AppServiceProvider::class,
    PagosServiceProvider::class,
    AdminPanelProvider::class,
];
