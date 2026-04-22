<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;

class OrderCheckoutService
{
    public function createPendingOrders(array $validated, int $userId): SupportCollection
    {
        return DB::transaction(function () use ($validated, $userId) {
            $items = collect($validated['items']);
            $products = $this->loadCheckoutProducts($items);

            $this->assertCheckoutProductsAreAvailable($items, $products);

            return $this->createOrdersForArtisans($validated, $items, $products, $userId);
        });
    }

    private function loadCheckoutProducts(SupportCollection $items): Collection
    {
        $productIds = $items->pluck('product_id')->unique()->all();

        $products = Product::query()
            ->whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        if ($products->count() !== count($productIds)) {
            throw new \RuntimeException('One or more products are unavailable.');
        }

        return $products;
    }

    private function assertCheckoutProductsAreAvailable(SupportCollection $items, Collection $products): void
    {
        foreach ($items as $item) {
            /** @var Product $product */
            $product = $products->get($item['product_id']);

            if (! $product->is_published || $product->status !== ProductStatus::Active) {
                throw new \RuntimeException('One or more products are not available for checkout.');
            }

            if ($product->stock < $item['quantity']) {
                throw new \RuntimeException("Not enough stock for {$product->name}.");
            }
        }
    }

    private function createOrdersForArtisans(
        array $validated,
        SupportCollection $items,
        Collection $products,
        int $userId,
    ): SupportCollection {
        $itemsByArtisan = $items->groupBy(fn ($item) => $products->get($item['product_id'])?->artisan_id);
        $createdOrders = collect();

        foreach ($itemsByArtisan as $artisanId => $artisanItems) {
            if (! $artisanId) {
                throw new \RuntimeException('One or more products are missing artisan ownership.');
            }

            $order = Order::create([
                'user_id' => $userId,
                'artisan_id' => $artisanId,
                'total_amount' => $this->calculateArtisanOrderTotal($artisanItems, $products),
                'shipping_address' => $validated['shipping_address'],
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'status' => OrderStatus::Pending,
            ]);

            foreach ($artisanItems as $item) {
                /** @var Product $product */
                $product = $products->get($item['product_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'artisan_id' => $product->artisan_id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ]);
            }

            $createdOrders->push($order);
        }

        return $createdOrders;
    }

    private function calculateArtisanOrderTotal(SupportCollection $artisanItems, Collection $products): float
    {
        return (float) $artisanItems->sum(function ($item) use ($products) {
            /** @var Product $product */
            $product = $products->get($item['product_id']);

            return (float) $product->price * (int) $item['quantity'];
        });
    }
}
