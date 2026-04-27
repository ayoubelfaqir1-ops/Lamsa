<?php

namespace App\Http\Middleware;

use App\Enums\ArtisanStatus;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureArtisanIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Ensure the user is authenticated and has an artisan profile
        if (!$user || !$user->artisan) {
            return redirect()->route('home');
        }

        // If the artisan is not active
        if ($user->artisan->status !== ArtisanStatus::Active) {
            // Allow access ONLY to the dashboard (where the pending view is shown)
            // to avoid infinite loops if it redirects to itself.
            if (!$request->routeIs('artisan.dashboard')) {
                return redirect()->route('artisan.dashboard');
            }
        }

        return $next($request);
    }
}
