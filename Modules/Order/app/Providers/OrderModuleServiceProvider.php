<?php

namespace Modules\Order\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Order\Models\Order;
use Modules\Order\Policies\OrderPolicy;

class OrderModuleServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configureRateLimiting();

        Gate::policy(Order::class, OrderPolicy::class);

        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/../../routes/api.php');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Configure rate limiters for checkout and order endpoints.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('checkout', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
    }
}
