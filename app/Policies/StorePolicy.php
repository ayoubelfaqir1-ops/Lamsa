<?php

namespace App\Policies;

use App\Models\Store;
use App\Models\User;

class StorePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Store $store): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('artisan');
    }

    public function update(User $user, Store $store): bool
    {
        if ($user->hasRole('admin')) return true;
        return $user->hasRole('artisan') && $user->artisan?->id === $store->artisan_id;
    }

    public function delete(User $user, Store $store): bool
    {
        if ($user->hasRole('admin')) return true;
        return $user->hasRole('artisan') && $user->artisan?->id === $store->artisan_id;
    }
}