<?php

namespace Modules\Product\Policies;

use Modules\Auth\Models\User;
use Modules\Product\Models\Product;
use Modules\Product\Models\Review;

class ReviewPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Review $review): bool
    {
        return true;
    }

    public function create(User $user, Product|int $product): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $productId = $product instanceof Product ? $product->id : $product;

        return ! Review::where('user_id', $user->id)->where('product_id', $productId)->exists();
    }

    public function update(User $user, Review $review): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $review->user_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $review->user_id === $user->id;
    }
}
