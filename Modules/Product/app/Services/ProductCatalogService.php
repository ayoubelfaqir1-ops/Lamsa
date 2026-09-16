<?php

namespace Modules\Product\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;

class ProductCatalogService
{
    /**
     * Get paginated products for the buyer catalog.
     */
    public function getPaginatedProducts(array $filters): LengthAwarePaginator
    {
        $query = Product::query()
            ->visible()
            ->with(['category', 'store'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');

        if (! empty($filters['q'])) {
            $query->search($filters['q']);
        }

        if (! empty($filters['category'])) {
            $query->inCategory($filters['category']);
        }

        if (isset($filters['min_price']) || isset($filters['max_price'])) {
            $query->inPriceRange($filters['min_price'] ?? null, $filters['max_price'] ?? null);
        }

        $this->applySorting($query, $filters['sort'] ?? 'newest');

        return $query->paginate($filters['per_page'] ?? 12);
    }

    /**
     * Get single product detail for buyers.
     */
    public function getProductBySlug(string $slug): Product
    {
        return Product::query()
            ->visible()
            ->where('slug', $slug)
            ->with([
                'category',
                'store.artisan.user',
                'artisan.user',
                'reviews' => fn ($q) => $q->latest()->take(5),
                'reviews.user',
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->firstOrFail();
    }

    /**
     * Get all active categories with their visible products count.
     */
    public function getActiveCategories(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->withCount([
                'products' => fn ($query) => $query->where('is_published', true)->where('status', ProductStatus::Active),
            ])
            ->orderBy('name')
            ->get();
    }

    /**
     * Apply requested sorting to the query.
     */
    private function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_low' => $query->orderBy('price'),
            'price_high' => $query->orderByDesc('price'),
            'name' => $query->orderBy('name'),
            'rating' => $query->orderByDesc('reviews_avg_rating')->latest(),
            default => $query->latest(), // 'newest' or fallback
        };
    }
}
