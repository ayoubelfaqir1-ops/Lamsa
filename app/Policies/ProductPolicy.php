<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

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
        if ($user->hasRole('admin')) return true;
        
        if (!$user->hasRole('artisan')) return false;
        
        // Artisan must have a store to create products
        return $user->artisan && $user->artisan->store;
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasRole('admin')) return true;
        return $user->hasRole('artisan') && $product->store && $user->artisan?->id === $product->store->artisan_id;
    }

    public function delete(User $user, Product $product): bool
    {
        if ($user->hasRole('admin')) return true;
        return $user->hasRole('artisan') && $product->store && $user->artisan?->id === $product->store->artisan_id;
    }
}
