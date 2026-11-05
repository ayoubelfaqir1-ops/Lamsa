<?php

namespace Modules\Store\Policies;

use Modules\Auth\Models\User;
use Modules\Store\Models\Store;

class StorePolicy
{
    /**
     * Determine whether the user can view the store.
     */
    public function view(?User $user, Store $store): bool
    {
        if ($store->is_active) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->hasRole('admin') || ($user->artisan?->id === $store->artisan_id);
    }

    /**
     * Determine whether the user can create a store.
     */
    public function create(User $user): bool
    {
        if (! $user->isArtisan() || ! $user->artisan) {
            return false;
        }

        // An artisan can only have one store
        return $user->artisan->store === null;
    }

    /**
     * Determine whether the user can update the store.
     */
    public function update(User $user, Store $store): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->isArtisan() && ($user->artisan?->id === $store->artisan_id);
    }

    /**
     * Determine whether the user can delete the store.
     */
    public function delete(User $user, Store $store): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->isArtisan() && ($user->artisan?->id === $store->artisan_id);
    }
}

