<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\Review;

class ProductDetailService
{
    public function build(Product $product): array
    {
        abort_unless(
            $product->is_published && $product->status === ProductStatus::Active,
            404
        );

        $product->load([
            'category',
            'store.artisan.user',
            'artisan.user',
            'reviews' => fn ($query) => $query->latest(),
            'reviews.user',
        ])->loadCount('reviews')
          ->loadAvg('reviews', 'rating');

        $store = $product->store;

        $storeProductCount = null;
        $storeReviewCount = null;
        $storeRating = null;

        if ($store) {
            $storeProductCount = Product::query()
                ->where('store_id', $store->id)
                ->where('is_published', true)
                ->where('status', ProductStatus::Active)
                ->count();

            $storeReviewCount = Review::query()
                ->whereHas('product', function ($query) use ($store) {
                    $query->where('store_id', $store->id);
                })
                ->count();

            $storeRating = round((float) (Review::query()
                ->whereHas('product', function ($query) use ($store) {
                    $query->where('store_id', $store->id);
                })
                ->avg('rating') ?? 0), 1);
        }

        $relatedProducts = Product::query()
            ->with(['category', 'store'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('is_published', true)
            ->where('status', ProductStatus::Active)
            ->whereKeyNot($product->getKey())
            ->where(function ($query) use ($product) {
                $query
                    ->where('artisan_id', $product->artisan_id)
                    ->orWhere('category_id', $product->category_id);
            })
            ->latest()
            ->take(4)
            ->get();

        return compact(
            'product',
            'relatedProducts',
            'storeProductCount',
            'storeReviewCount',
            'storeRating'
        );
    }
}
