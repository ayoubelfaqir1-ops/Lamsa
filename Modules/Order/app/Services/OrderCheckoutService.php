<?php

namespace Modules\Order\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Cart;
use Modules\Order\Models\CartItem;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Product;
use RuntimeException;

class OrderCheckoutService
{
    /**
     * Process checkout for the authenticated user from their database cart.
     *
     * @param  array{shipping_address: string, payment_method?: string, notes?: string|null}  $validated
     * @return Collection<int, Order>
     */
    public function checkout(User $user, array $validated): Collection
    {
        return DB::transaction(function () use ($user, $validated) {
            /** @var Cart|null $cart */
            $cart = Cart::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if (! $cart) {
                throw new RuntimeException('Your cart is empty.');
            }

            /** @var Collection<int, CartItem> $cartItems */
            $cartItems = $cart->items()->with('product')->get();

            if ($cartItems->isEmpty()) {
                throw new RuntimeException('Your cart is empty.');
            }

            $productIds = $cartItems->pluck('product_id')->unique()->all();

            /** @var \Illuminate\Database\Eloquent\Collection<int, Product> $products */
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $this->validateProductsAvailability($cartItems, $products);

            // Group items by artisan to create separate orders per vendor
            $itemsByArtisan = $cartItems->groupBy(fn (CartItem $item) => $products->get($item->product_id)?->artisan_id);
            $createdOrders = collect();

            $paymentMethod = $validated['payment_method'] ?? 'cash';
            $paymentStatus = $paymentMethod === 'card' ? 'unpaid' : 'cash_on_delivery';

            foreach ($itemsByArtisan as $artisanId => $artisanItems) {
                if (! $artisanId) {
                    throw new RuntimeException('One or more products are missing artisan ownership.');
                }

                $orderTotal = (float) $artisanItems->sum(function (CartItem $item) use ($products) {
                    $product = $products->get($item->product_id);

                    return (float) ($product ? $product->price : 0) * $item->quantity;
                });

                /** @var Order $order */
                $order = Order::create([
                    'user_id' => $user->id,
                    'artisan_id' => $artisanId,
                    'status' => OrderStatus::Pending,
                    'total_amount' => $orderTotal,
                    'shipping_address' => $validated['shipping_address'],
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($artisanItems as $cartItem) {
                    /** @var Product $product */
                    $product = $products->get($cartItem->product_id);

                    // Snapshot product details and decrement inventory
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'artisan_id' => $artisanId,
                        'product_name' => $product->name,
                        'quantity' => $cartItem->quantity,
                        'unit_price' => $product->price,
                    ]);

                    $product->decrement('stock', $cartItem->quantity);
                }

                $createdOrders->push($order->load(['items.product', 'artisan.user']));
            }

            // Clear the buyer's cart after successful checkout
            $cart->items()->delete();

            return $createdOrders;
        });
    }

    /**
     * @param  Collection<int, CartItem>  $cartItems
     * @param  \Illuminate\Database\Eloquent\Collection<int, Product>  $products
     */
    private function validateProductsAvailability(Collection $cartItems, \Illuminate\Database\Eloquent\Collection $products): void
    {
        foreach ($cartItems as $item) {
            /** @var Product|null $product */
            $product = $products->get($item->product_id);
            $productName = $product->name ?? $item->product->name ?? 'item';

            if (! $product || ! $product->is_published || $product->status !== ProductStatus::Active) {
                throw new RuntimeException("Product '{$productName}' is no longer available.");
            }

            if ($product->stock < $item->quantity) {
                throw new RuntimeException("Not enough stock for '{$product->name}' ({$product->stock} remaining).");
            }
        }
    }
}
