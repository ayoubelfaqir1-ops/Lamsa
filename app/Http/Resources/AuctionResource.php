<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuctionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'images'         => $this->images ?? [],
            'status'         => $this->status->value,
            'starting_price' => $this->starting_price,
            'current_price'  => $this->current_price ?? $this->starting_price,
            'reserve_price'  => $this->when(
                $request->user()?->isAdmin(),
                $this->reserve_price
            ),
            'starts_at'      => $this->starts_at,
            'ends_at'        => $this->ends_at,
            'bids_count'     => $this->bids_count ?? $this->bids()->count(),
            'highest_bid'    => $this->whenLoaded('highestBid', fn () => [
                'amount' => $this->highestBid?->amount,
                'user'   => $this->highestBid?->user?->name,
            ]),
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
        ];
    }
}
