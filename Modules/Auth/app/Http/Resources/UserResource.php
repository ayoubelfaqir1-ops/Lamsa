<?php

namespace Modules\Auth\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'avatar' => $this->avatar,
            'role' => $this->roles->pluck('name')->first() ?? 'buyer',
            'is_profile_complete' => $this->hasCompleteCheckoutProfile(),
            'artisan_profile' => $this->when($this->isArtisan(), function () {
                return $this->artisan ? [
                    'id' => $this->artisan->id,
                    'bio' => $this->artisan->bio,
                    'city' => $this->artisan->city,
                    'region' => $this->artisan->region,
                    'status' => $this->artisan->status,
                    'craft_type' => $this->artisan->craft_type,
                ] : null;
            }),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
