<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Collection;

class OrderPaymentService
{
    public function loadPendingBuyerOrders(string $orderIdsString, int $userId): Collection
    {
        $orderIds = collect(explode(',', $orderIdsString))
            ->map(fn ($id) => (int) trim($id))
            ->filter()
            ->unique()
            ->values();

        if ($orderIds->isEmpty()) {
            throw new \RuntimeException('No pending orders were provided for payment.');
        }

        $orders = Order::query()
            ->with(['items.product', 'items.product.store'])
            ->where('user_id', $userId)
            ->whereIn('id', $orderIds)
            ->get();

        if ($orders->count() !== $orderIds->count()) {
            throw new \RuntimeException('Some selected orders could not be found.');
        }

        if ($orders->contains(fn (Order $order) => $order->status !== OrderStatus::Pending)) {
            throw new \RuntimeException('Only pending orders can continue to payment.');
        }

        return $orders;
    }

    public function confirmCashOnDelivery(Collection $orders): void
    {
        $this->updateOrders($orders, 'cash', 'cash_on_delivery', OrderStatus::Processing);
    }

    public function confirmCardPayment(Collection $orders): void
    {
        $this->updateOrders($orders, 'card', 'paid', OrderStatus::Processing);
    }

    private function updateOrders(Collection $orders, string $paymentMethod, string $paymentStatus, OrderStatus $status): void
    {
        foreach ($orders as $order) {
            $order->update([
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'status' => $status,
            ]);
        }
    }
}
