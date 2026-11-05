<?php

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\Models\Artisan;

/**
 * @mixin Artisan
 */
class AdminArtisanResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'name' => $this->user?->name,
            'email' => $this->user?->email,
            'phone' => $this->user?->phone,
            'bio' => $this->bio,
            'city' => $this->city,
            'region' => $this->region,
            'craft_type' => $this->craft_type,
            'status' => $this->status->value,
            'store' => $this->whenLoaded('store', fn () => $this->store ? [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
            ] : null),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
