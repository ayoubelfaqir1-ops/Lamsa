<?php

namespace Modules\Auction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auction\Models\Bid;

/**
 * @mixin Bid
 */
class BidResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'auction_id' => $this->auction_id,
            'amount' => (float) $this->amount,
            'bidder' => [
                'id' => $this->user_id,
                'name' => $this->user->name,
            ],
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
