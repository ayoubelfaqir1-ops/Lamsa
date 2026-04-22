<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileIsComplete
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! $user->hasCompleteCheckoutProfile()) {
            return redirect()
                ->route('profile')
                ->with('error', 'Please complete your phone number and address before checking out.');
        }

        return $next($request);
    }
}
