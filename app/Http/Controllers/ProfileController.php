<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $orders = Order::query()
            ->with(['items.product.store'])
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $bids = $user->bids()
            ->with(['auction.store', 'auction.category'])
            ->latest()
            ->take(5)
            ->get();

        $stats = [
            'orders' => Order::query()->where('user_id', $user->id)->count(),
            'active_orders' => Order::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['pending', 'processing', 'shipped'])
                ->count(),
            'bids' => $user->bids()->count(),
            'won_bids' => $user->bids()
                ->whereHas('auction', function ($query) {
                    $query->where('ends_at', '<=', now());
                })
                ->count(),
        ];

        return view('profile', compact('user', 'orders', 'bids', 'stats'));
    }
}
