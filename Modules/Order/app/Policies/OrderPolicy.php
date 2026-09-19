<?php

namespace Modules\Order\Policies;

use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($order->user_id === $user->id) {
            return true;
        }

        return $user->artisan !== null && $order->artisan_id === $user->artisan->id;
    }

    public function create(User $user): bool
    {
        return $user->isBuyer() || $user->isAdmin();
    }

    public function cancel(User $user, Order $order): bool
    {
        if ($order->status !== OrderStatus::Pending) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $order->user_id === $user->id;
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->artisan !== null && $order->artisan_id === $user->artisan->id;
    }
}
