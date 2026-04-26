<?php

namespace App\Policies;

use App\Models\Artisan;
use App\Models\User;

class ArtisanPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Artisan $artisan): bool
    {
        if ($user->isAdmin()) return true;
        return $user->artisan?->id === $artisan->id;
    }

    public function create(User $user): bool
    {
        return $user->isArtisan();
    }

    public function update(User $user, Artisan $artisan): bool
    {
        if ($user->isAdmin()) return true;
        return $user->artisan?->id === $artisan->id;
    }

    public function delete(User $user, Artisan $artisan): bool
    {
        return $user->isAdmin();
    }
}