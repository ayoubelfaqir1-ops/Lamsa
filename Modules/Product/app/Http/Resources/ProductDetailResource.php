<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\Review;

/**
 * @mixin Product
 */
class ProductDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'images' => $this->images ?? [],
            'is_published' => $this->is_published,
            'review_summary' => [
                'average_rating' => round((float) ($this->reviews_avg_rating ?? 0), 1),
                'count' => $this->reviews_count ?? 0,
            ],
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ],
            'store' => [
                'id' => $this->store?->id,
                'name' => $this->store?->name,
                'slug' => $this->store?->slug,
                'rating' => $this->store && $this->store->rating !== null ? round((float) $this->store->rating, 1) : 0.0,
            ],
            'artisan' => [
                'id' => $this->artisan?->id,
                'name' => $this->artisan?->user?->name,
                'city' => $this->artisan?->city,
                'region' => $this->artisan?->region,
                'bio' => $this->artisan?->bio,
            ],
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(fn (Review $review) => [
                    'id' => $review->id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'user' => [
                        'name' => $review->user?->name,
                    ],
                    'created_at' => $review->created_at,
                ]);
            }),
            'created_at' => $this->created_at,
        ];
    }
}
