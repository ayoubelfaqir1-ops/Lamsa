<?php

use App\Providers\AppServiceProvider;
use Modules\Auction\Providers\AuctionModuleServiceProvider;
use Modules\Auth\Providers\AuthModuleServiceProvider;
use Modules\Order\Providers\OrderModuleServiceProvider;
use Modules\Product\Providers\ProductModuleServiceProvider;

return [
    AppServiceProvider::class,
    AuthModuleServiceProvider::class,
    ProductModuleServiceProvider::class,
    OrderModuleServiceProvider::class,
    AuctionModuleServiceProvider::class,
];
