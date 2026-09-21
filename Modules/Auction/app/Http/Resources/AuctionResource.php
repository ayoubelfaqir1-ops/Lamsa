<?php

namespace Modules\Auction\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auction\Models\Auction;
use Modules\Auth\Models\User;

/**
 * @mixin Auction
 */
class AuctionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();

        // Privacy rule: Only the auction owner artisan or an admin can see the reserve price
        $canSeeReserve = $user !== null
            && ($user->isAdmin() || ($user->isArtisan() && $this->artisan->user_id === $user->id));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'images' => $this->images ?? [],
            'starting_price' => (float) $this->starting_price,
            'current_price' => (float) $this->currentBidAmount(),
            'reserve_price' => $canSeeReserve ? (float) $this->reserve_price : null,
            'minimum_next_bid' => (float) $this->minimumNextBid(),
            'status' => $this->status->value,
            'is_published' => $this->is_published,
            'can_accept_bids' => $this->canAcceptBids(),
            'starts_at' => $this->starts_at->toIso8601String(),
            'ends_at' => $this->ends_at->toIso8601String(),
            'bids_count' => $this->bids_count ?? $this->bids()->count(),
            'highest_bid' => $this->relationLoaded('highestBid') && $this->highestBid
                ? new BidResource($this->highestBid)
                : null,
            'winner' => $this->winner_id ? [
                'id' => $this->winner_id,
                'name' => $this->winner?->name,
            ] : null,
            'category' => $this->relationLoaded('category') ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null,
            'store' => $this->relationLoaded('store') ? [
                'id' => $this->store->id,
                'name' => $this->store->name,
                'slug' => $this->store->slug,
            ] : null,
            'artisan' => $this->relationLoaded('artisan') ? [
                'id' => $this->artisan->id,
                'craft_type' => $this->artisan->craft_type,
                'name' => $this->artisan->user?->name,
            ] : null,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
