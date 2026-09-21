<?php

namespace Modules\Auction\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\Auction\Console\Commands\CloseExpiredAuctionsCommand;
use Modules\Auction\Models\Auction;
use Modules\Auction\Policies\AuctionPolicy;

class AuctionModuleServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->configureRateLimiting();

        if ($this->app->runningInConsole()) {
            $this->commands([
                CloseExpiredAuctionsCommand::class,
            ]);
        }
        Gate::policy(Auction::class, AuctionPolicy::class);

        Route::middleware('api')
            ->prefix('api/v1')
            ->group(__DIR__.'/../../routes/api.php');

        if (file_exists(__DIR__.'/../../routes/channels.php')) {
            require __DIR__.'/../../routes/channels.php';
        }

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('bidding', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
