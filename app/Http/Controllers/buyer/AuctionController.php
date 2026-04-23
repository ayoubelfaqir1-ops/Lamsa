<?php

namespace App\Http\Controllers\Buyer;

use App\Enums\AuctionStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCategory = trim((string) $request->string('category'));
        $sort = trim((string) $request->string('sort', 'ending_soon'));

        $auctions = Auction::query()
            ->with(['store', 'category', 'highestBid'])
            ->withCount('bids')
            ->where('status', AuctionStatus::Active)
            ->where('ends_at', '>', now())
            ->published()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($selectedCategory !== '', function ($query) use ($selectedCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($selectedCategory) {
                    $categoryQuery->where('slug', $selectedCategory);
                });
            });

        $auctions = match ($sort) {
            'price_low' => $auctions->orderBy('current_price'),
            'price_high' => $auctions->orderByDesc('current_price'),
            'newest' => $auctions->latest(),
            default => $auctions->orderBy('ends_at'),
        };

        $auctions = $auctions
            ->paginate(12);

        $categories = Category::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'image']);

        return view('auctions.index', compact(
            'auctions',
            'categories',
            'search',
            'selectedCategory',
            'sort',
        ));
    }

    public function show(Auction $auction): View
    {
        abort_unless(
            $auction->is_published
                && $auction->status === AuctionStatus::Active,
            404
        );

        $auction->load(['store', 'category', 'bids.user']);

        return view('auctions.show', compact('auction'));
    }
}
