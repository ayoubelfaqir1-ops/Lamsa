<?php

namespace Modules\Order\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use RuntimeException;

class OrderService
{
    /**
     * Get paginated orders placed by the buyer.
     */
    public function getBuyerOrders(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return Order::query()
            ->with(['items.product', 'artisan.user'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Cancel an order if it is still pending.
     */
    public function cancelOrder(Order $order): Order
    {
        if ($order->status !== OrderStatus::Pending) {
            throw new RuntimeException('Only pending orders can be cancelled.');
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        // Restore stock when order is cancelled
        foreach ($order->items as $item) {
            $item->product?->increment('stock', $item->quantity);
        }

        return $order->fresh(['items.product', 'artisan.user']);
    }

    /**
     * Get paginated orders received by the artisan.
     */
    public function getArtisanOrders(User $user, int $perPage = 10): LengthAwarePaginator
    {
        $artisan = $user->artisan;

        if (! $artisan) {
            throw new RuntimeException('User does not have an artisan profile.');
        }

        return Order::query()
            ->with(['items.product', 'user'])
            ->where('artisan_id', $artisan->id)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Update order status with state machine transition checks.
     */
    public function updateStatus(Order $order, OrderStatus $newStatus): Order
    {
        if (! $this->canTransition($order->status, $newStatus)) {
            throw new RuntimeException("Cannot transition order from {$order->status->value} to {$newStatus->value}.");
        }

        $order->update(['status' => $newStatus]);

        return $order->fresh(['items.product', 'user']);
    }

    /**
     * State machine transition rules.
     */
    public function canTransition(OrderStatus $current, OrderStatus $next): bool
    {
        return match ($current) {
            OrderStatus::Pending => in_array($next, [OrderStatus::Processing, OrderStatus::Cancelled], true),
            OrderStatus::Processing => in_array($next, [OrderStatus::Shipped, OrderStatus::Cancelled], true),
            OrderStatus::Shipped => $next === OrderStatus::Delivered,
            default => false,
        };
    }
}
