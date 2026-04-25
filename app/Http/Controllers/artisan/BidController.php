<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\ArtisanStatus;
use App\Http\Controllers\Controller;
use App\Models\Bid;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BidController extends Controller
{
    /**
     * Display a listing of bids received on the artisan's auctions.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $artisan = $user->artisan;

        if (!$artisan && !$user->hasRole('artisan')) {
            return redirect()->route('home');
        }

        if (!$artisan || $artisan->status === ArtisanStatus::Pending) {
            return view('artisan.pending', [
                'status' => ArtisanStatus::Pending,
            ]);
        }

        if ($artisan->status !== ArtisanStatus::Active) {
            return view('artisan.pending', [
                'status' => $artisan->status,
            ]);
        }

        $bids = Bid::query()
            ->with(['user', 'auction'])
            ->whereHas('auction', function ($query) use ($artisan) {
                $query->where('artisan_id', $artisan->id);
            })
            ->latest()
            ->paginate(10);

        return view('artisan.bids.index', compact('bids'));
    }
}
