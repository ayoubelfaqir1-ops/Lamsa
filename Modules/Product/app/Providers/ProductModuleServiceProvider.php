<?php

namespace Modules\Product\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\Review;
use Modules\Product\Policies\CategoryPolicy;
use Modules\Product\Policies\ProductPolicy;
use Modules\Product\Policies\ReviewPolicy;

class ProductModuleServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configureRateLimiting();

        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Review::class, ReviewPolicy::class);

        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/../../routes/api.php');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Configure rate limiters for catalog endpoints.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('catalog', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
    }
}
