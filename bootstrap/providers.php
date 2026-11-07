<?php

use App\Providers\AppServiceProvider;
use Modules\Auction\Providers\AuctionModuleServiceProvider;
use Modules\Auth\Providers\AuthModuleServiceProvider;
use Modules\CMS\Providers\CMSModuleServiceProvider;
use Modules\Order\Providers\OrderModuleServiceProvider;
use Modules\Product\Providers\ProductModuleServiceProvider;
use Modules\Store\Providers\StoreModuleServiceProvider;

return [
    AppServiceProvider::class,
    AuthModuleServiceProvider::class,
    ProductModuleServiceProvider::class,
    OrderModuleServiceProvider::class,
    AuctionModuleServiceProvider::class,
    StoreModuleServiceProvider::class,
    CMSModuleServiceProvider::class,
];
