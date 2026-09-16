<?php

namespace Modules\Product\Policies;

use Modules\Auth\Models\User;
use Modules\Product\Models\Product;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        if (! $user->hasRole('artisan')) {
            return false;
        }

        // Artisan must have a store to create products
        /** @var \Modules\Auth\Models\Artisan|null $artisan */
        $artisan = $user->artisan;
        
        return $artisan && $artisan->store;
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        /** @var \Modules\Auth\Models\Artisan|null $artisan */
        $artisan = $user->artisan;

        return $user->hasRole('artisan') && $product->store && $artisan?->id === $product->store->artisan_id;
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        /** @var \Modules\Auth\Models\Artisan|null $artisan */
        $artisan = $user->artisan;

        return $user->hasRole('artisan') && $product->store && $artisan?->id === $product->store->artisan_id;
    }
}
