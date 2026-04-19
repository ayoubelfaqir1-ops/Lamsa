<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request and redirect to the appropriate dashboard.
     */
    public function __invoke(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('artisan')) {
            return redirect()->route('artisan.dashboard');
        }
        
        return redirect()->route('home');
    }
}
