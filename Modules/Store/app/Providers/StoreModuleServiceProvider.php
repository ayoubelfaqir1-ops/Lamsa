<?php

namespace Modules\Store\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Store\Models\Store;
use Modules\Store\Policies\StorePolicy;

class StoreModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Service container bindings if needed
    }

    public function boot(): void
    {
        Gate::policy(Store::class, StorePolicy::class);

        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/../../routes/api.php');
    }
}

