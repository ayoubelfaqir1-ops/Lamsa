<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) return true;
        return $order->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('buyer') || $user->hasRole('admin');
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasRole('admin');
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($user->hasRole('admin')) return true;
        return $order->user_id === $user->id;
    }
}
