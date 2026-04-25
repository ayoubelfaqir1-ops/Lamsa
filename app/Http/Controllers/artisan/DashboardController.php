<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Enums\ArtisanStatus;
use App\Services\ArtisanDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ArtisanDashboardService $artisanDashboardService,
    ) {
    }

    /**
     * Display the artisan studio dashboard or a pending approval page.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $artisan = $user->artisan;

        // If the user has no artisan profile and is just a buyer, redirect them out
        if (!$artisan && !$user->hasRole('artisan')) {
            return redirect()->route('home');
        }

        // If no artisan record exists yet but they have the role, or status is pending, show pending
        if (!$artisan || $artisan->status === ArtisanStatus::Pending) {
            return view('artisan.pending', [
                'status' => ArtisanStatus::Pending
            ]);
        }

        // Handle other non-active statuses (Suspended, Rejected)
        if ($artisan->status !== ArtisanStatus::Active) {
            return view('artisan.pending', [
                'status' => $artisan->status
            ]);
        }

        $dashboardData = Cache::remember(
            "artisan.dashboard.{$artisan->id}",
            now()->addMinutes(5),
            fn () => $this->artisanDashboardService->getDashboardData($artisan)
        );

        return view('artisan.dashboard', $dashboardData);
    }
}
