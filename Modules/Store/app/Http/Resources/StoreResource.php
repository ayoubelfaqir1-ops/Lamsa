<?php

namespace Modules\Store\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Models\Store;

/**
 * @mixin Store
 */
class StoreResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'artisan_id' => $this->artisan_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'logo_url' => $this->logo ? Storage::disk('public')->url($this->logo) : null,
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'artisan' => $this->whenLoaded('artisan', fn () => [
                'id' => $this->artisan?->id,
                'name' => $this->artisan?->user?->name,
                'bio' => $this->artisan?->bio,
                'craft_type' => $this->artisan?->craft_type,
                'city' => $this->artisan?->city,
                'region' => $this->artisan?->region,
                'status' => $this->artisan?->status->value,
            ]),
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
        ];
    }
}
