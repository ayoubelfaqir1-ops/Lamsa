<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Review;
use App\Models\Store;

class StoreDetailService
{
    public function build(Store $store): array
    {
        abort_unless($store->is_active, 404);

        $store->load('artisan.user');

        $productsQuery = $store->products()
            ->with(['category', 'store'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('is_published', true)
            ->where('status', ProductStatus::Active);

        $products = (clone $productsQuery)->paginate(12);
        $storeProductCount = (clone $productsQuery)->count();

        $storeReviewQuery = Review::query()
            ->with(['user', 'product'])
            ->whereHas('product', function ($query) use ($store) {
                $query->where('store_id', $store->id)
                    ->where('is_published', true)
                    ->where('status', ProductStatus::Active);
            });

        $storeReviewCount = (clone $storeReviewQuery)->count();
        $storeRating = round((float) ((clone $storeReviewQuery)->avg('rating') ?? 0), 1);
        $recentReviews = (clone $storeReviewQuery)
            ->latest()
            ->take(6)
            ->get();

        return compact(
            'store',
            'products',
            'storeProductCount',
            'storeReviewCount',
            'storeRating',
            'recentReviews'
        );
    }
}
