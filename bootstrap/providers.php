<?php

use App\Providers\AppServiceProvider;
use Modules\Auth\Providers\AuthModuleServiceProvider;
use Modules\Product\Providers\ProductModuleServiceProvider;

return [
    AppServiceProvider::class,
    AuthModuleServiceProvider::class,
    ProductModuleServiceProvider::class,
];
