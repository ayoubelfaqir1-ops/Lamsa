<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'price'          => $this->price,
            'stock'          => $this->stock,
            'images'         => $this->images ?? [],
            'is_published'   => $this->is_published,
            'average_rating' => round($this->average_rating, 1),
            'category'       => [
                'id'   => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'store'          => [
                'id'   => $this->store?->id,
                'name' => $this->store?->name,
                'slug' => $this->store?->slug,
            ],
            'artisan'        => [
                'id'   => $this->artisan?->id,
                'name' => $this->artisan?->user?->name,
            ],
            'created_at'     => $this->created_at,
        ];
    }
}
