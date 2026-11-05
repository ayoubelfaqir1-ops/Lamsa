<?php

namespace Modules\Product\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Product\Models\Review;

/**
 * @mixin Review
 */
class ReviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}

