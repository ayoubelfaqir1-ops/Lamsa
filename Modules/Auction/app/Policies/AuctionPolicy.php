<?php

namespace Modules\Auction\Policies;

use App\Enums\ArtisanStatus;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Models\Auction;
use Modules\Auth\Models\User;

class AuctionPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Auction $auction): bool
    {
        if ($auction->is_published) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $auction->artisan->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->isArtisan()
            && $user->artisan !== null
            && $user->artisan->status === ArtisanStatus::Active;
    }

    public function update(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $auction->artisan->user_id === $user->id
            && ! $auction->bids()->exists()
            && $auction->starts_at->isFuture();
    }

    public function cancel(User $user, Auction $auction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $auction->artisan->user_id === $user->id
            && $auction->status !== AuctionStatus::Ended
            && $auction->status !== AuctionStatus::Cancelled;
    }

    public function delete(User $user, Auction $auction): bool
    {
        if ($auction->bids()->exists()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $auction->artisan->user_id === $user->id;
    }

    public function bid(User $user, Auction $auction): bool
    {
        // Artisan cannot bid on their own auction (anti-shill rule)
        if ($auction->artisan->user_id === $user->id) {
            return false;
        }

        return $auction->canAcceptBids();
    }
}
