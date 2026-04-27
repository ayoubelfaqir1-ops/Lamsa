<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user, int $productId): bool
    {
        if ($user->isAdmin()) return true;

        // Buyer can only review purchased products
        return $user->orders()
            ->whereIn('status', [OrderStatus::Delivered->value])
            ->whereHas('items', fn ($q) => $q->where('product_id', $productId))
            ->exists();
    }

    public function update(User $user, Review $review): bool
    {
        if ($user->isAdmin()) return true;
        return $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        if ($user->isAdmin()) return true;
        return $review->user_id === $user->id;
    }
}
