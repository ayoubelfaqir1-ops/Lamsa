<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Auction $auction): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) return true;
        
        if (!$user->hasRole('artisan')) return false;
        
        // Artisan must have a store to create auctions
        return $user->artisan && $user->artisan->store;
    }

    public function update(User $user, Auction $auction): bool
    {
        if ($user->hasRole('admin')) return true;
        return $user->hasRole('artisan')
            && $auction->artisan_id === $user->artisan?->id;
    }

    public function delete(User $user, Auction $auction): bool
    {
        if ($user->hasRole('admin')) return true;
        return $user->hasRole('artisan')
            && $auction->artisan_id === $user->artisan?->id;
    }

    public function bid(User $user, Auction $auction): bool
    {
        if (!$user->hasRole('buyer')) return false;

        // Prevent auction owners from bidding on their own auctions
        if ($user->artisan && $user->artisan->id === $auction->artisan_id) {
            return false;
        }

        return $auction->canAcceptBids();
    }
}
