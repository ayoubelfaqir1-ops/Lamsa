<?php

namespace App\Services;

use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Collection;
use RuntimeException;

class CartService
{
    public function getDisplayItems(): Collection
    {
        if (auth()->check()) {
            $this->syncGuestCartToAuthenticatedCart();

            return $this->getItemsAuthenticatedCart();
        }

        return $this->getItemsGuestCart();
    }

    public function getCheckoutItems(): Collection
    {
        if (! auth()->check()) {
            return collect();
        }

        return $this->getOrCreateAuthenticatedCart()->items()
            ->withWhereHas('product', function ($query) {
                $query->where('status', ProductStatus::Active)
                    ->where('is_published', true);
            })
            ->with(['product.category', 'product.store'])
            ->get()
            ->map(function ($item) {
                $item->product->quantity = $item->quantity;
                $item->product->cart_item_id = $item->id;

                return $item->product;
            });
    }

    public function clear(): void
    {
        if (auth()->check()) {
            $this->getOrCreateAuthenticatedCart()->items()->delete();
        }

        session()->forget('cart');
    }

    public function add(Product $product, int $quantity): void
    {
        if (
            ! $product->is_published
            || $product->status !== ProductStatus::Active
        ) {
            throw new RuntimeException('This product is not available for direct purchase.');
        }
        if ($product->stock <= 0) {
            throw new RuntimeException('Product is out of stock.');
        }

        if (auth()->check()) {
            $cart = $this->getOrCreateAuthenticatedCart();
            $item = $cart->items()->firstWhere('product_id', $product->id);
            $currentQuantity = $item?->quantity ?? 0;

            if ($currentQuantity + $quantity > $product->stock) {
                throw new RuntimeException('Not enough stock available.');
            }

            $cart->items()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $currentQuantity + $quantity],
            );

            return;
        }

        $cart = session('cart', []);
        $currentQuantity = $cart[$product->id]['quantity'] ?? 0;

        if ($currentQuantity + $quantity > $product->stock) {
            throw new RuntimeException('Not enough stock available.');
        }

        $cart[$product->id] = [
            'product_id' => $product->id,
            'quantity' => $currentQuantity + $quantity,
            'added_at' => now(),
        ];

        session(['cart' => $cart]);
    }

    public function update(Product $product, int $quantity): void
    {
        if (
            ! $product->is_published
            || $product->status !== ProductStatus::Active
        ) {
            throw new RuntimeException('This product is not available for direct purchase.');
        }

        if ($quantity > $product->stock) {
            throw new RuntimeException('Requested quantity exceeds available stock.');
        }

        if (auth()->check()) {
            $cart = $this->getOrCreateAuthenticatedCart();
            $item = $cart->items()->firstWhere('product_id', $product->id);

            if (! $item) {
                throw new RuntimeException('Product not in cart.');
            }

            $item->update(['quantity' => $quantity]);

            return;
        }

        $cart = session('cart', []);

        if (! isset($cart[$product->id])) {
            throw new RuntimeException('Product not in cart.');
        }

        $cart[$product->id]['quantity'] = $quantity;
        session(['cart' => $cart]);
    }

    public function remove(Product $product): void
    {
        if (auth()->check()) {
            $cart = auth()->user()->cart;

            if ($cart) {
                $cart->items()->where('product_id', $product->id)->delete();
            }

            return;
        }

        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            unset($cart[$product->id]);
            session(['cart' => $cart]);
        }
    }

    private function getOrCreateAuthenticatedCart(): Cart
    {
        return auth()->user()->cart()->firstOrCreate();
    }

    private function getItemsAuthenticatedCart(): Collection
    {
        return $this->getOrCreateAuthenticatedCart()->items()
            ->withWhereHas('product', function ($query) {
                $query->where('status', ProductStatus::Active)
                    ->where('is_published', true);
            })
            ->with(['product.category', 'product.store'])
            ->get()
            ->map(function ($item) {
                $item->product->quantity = $item->quantity;

                return $item->product;
            });
    }

    private function getItemsGuestCart(): Collection
    {
        $cart = session('cart', []);
        $productIds = array_keys($cart);

        if ($productIds === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', $productIds)
            ->where('is_published', true)
            ->where('status', ProductStatus::Active)
            ->with(['category', 'store'])
            ->get()
            ->map(function (Product $product) use ($cart) {
                $product->quantity = $cart[$product->id]['quantity'] ?? 1;

                return $product;
            });
    }

    private function syncGuestCartToAuthenticatedCart(): void
    {
        if (! session('cart')) {
            return;
        }

        $guestCartItems = $this->getItemsGuestCart();

        if ($guestCartItems->isEmpty()) {
            session()->forget('cart');

            return;
        }

        $cart = $this->getOrCreateAuthenticatedCart();

        foreach ($guestCartItems as $item) {
            $existingItem = $cart->items()->firstWhere('product_id', $item->id);
            $mergedQuantity = ($existingItem?->quantity ?? 0) + $item->quantity;

            if ($mergedQuantity > $item->stock) {
                $mergedQuantity = $item->stock;
            }

            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $mergedQuantity,
                ]);

                continue;
            }

            $cart->items()->create([
                'product_id' => $item->id,
                'quantity' => $mergedQuantity,
            ]);
        }

        session()->forget('cart');
    }
}
