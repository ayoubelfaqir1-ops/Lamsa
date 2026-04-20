<?php

namespace App\Http\Controllers;

use App\Enums\AuctionStatus;
use App\Enums\ProductStatus;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $featuredCategories = Category::query()
            ->active()
            ->whereHas('products', function ($query) {
                $query
                    ->where('is_published', true)
                    ->where('status', ProductStatus::Active);
            })
            ->take(5)
            ->get();

        $featuredAuctions = Auction::query()
            ->with(['store', 'highestBid'])
            ->withCount('bids')
            ->where('status', AuctionStatus::Active)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->published()
            ->orderByDesc('current_price')
            ->take(4)
            ->get();

        $productCollections = $featuredCategories
            ->map(function (Category $category) {
                $products = Product::query()
                    ->with(['store', 'category'])
                    ->withAvg('reviews', 'rating')
                    ->withCount('reviews')
                    ->where('category_id', $category->id)
                    ->where('is_published', true)
                    ->where('status', ProductStatus::Active)
                    ->latest()
                    ->take(4)
                    ->get();

                return [
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'products' => $products->take(4)->values(),
                ];
            })
            ->filter(fn (array $collection) => $collection['products']->isNotEmpty())
            ->values();

        return view('home', [
            'productCollections' => $productCollections,
            'featuredAuctions' => $featuredAuctions,
            'defaultCategory' => $productCollections->first()['slug'] ?? 'featured',
        ]);
    }
}
