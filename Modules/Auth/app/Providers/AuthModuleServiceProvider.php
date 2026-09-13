<?php

namespace Modules\Auth\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AuthModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->configurePasswordDefaults();
        $this->configureRateLimiting();

        Route::middleware('api')
            ->prefix('api/v1/auth')
            ->group(__DIR__ . '/../../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'auth');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Auth\Console\CreateAdminCommand::class,
            ]);
        }
    }

    /**
     * Configure default password validation rules for the Auth module.
     */
    protected function configurePasswordDefaults(): void
    {
        Password::defaults(fn () => Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->uncompromised()
        );
    }

    /**
     * Configure rate limiters for authentication endpoints.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            $email = strtolower(trim((string) $request->input('email', '')));

            return [
                Limit::perMinute(5)->by($email ?: $request->ip()),
                Limit::perMinute(10)->by($request->ip()),
            ];
        });
    }
}

