<?php

namespace Modules\Order\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\Models\User;
use Modules\Order\Models\Cart;
use Modules\Order\Models\CartItem;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Product;
use RuntimeException;

class CartService
{
    /**
     * Get or create the authenticated user's cart with items and products loaded.
     */
    public function getCart(User $user): Cart
    {
        $cart = $this->getOrCreateCart($user);

        $cart->load([
            'items.product' => function ($query) {
                $query->with(['category', 'store']);
            },
        ]);

        return $cart;
    }

    /**
     * Add a product to the user's cart.
     */
    public function addItem(User $user, Product $product, int $quantity): CartItem
    {
        $this->assertProductIsPurchasable($product);

        $cart = $this->getOrCreateCart($user);
        /** @var CartItem|null $item */
        $item = $cart->items()->firstWhere('product_id', $product->id);
        $currentQuantity = $item ? (int) $item->quantity : 0;
        $newQuantity = $currentQuantity + $quantity;

        if ($newQuantity > $product->stock) {
            throw new RuntimeException("Requested quantity exceeds available stock ({$product->stock} available).");
        }

        /** @var CartItem $cartItem */
        $cartItem = $cart->items()->updateOrCreate(
            ['product_id' => $product->id],
            ['quantity' => $newQuantity],
        );

        return $cartItem->load(['product.category', 'product.store']);
    }

    /**
     * Update the quantity of an existing item in the user's cart.
     */
    public function updateItem(User $user, Product $product, int $quantity): CartItem
    {
        $this->assertProductIsPurchasable($product);

        $cart = $this->getOrCreateCart($user);
        /** @var CartItem|null $item */
        $item = $cart->items()->firstWhere('product_id', $product->id);

        if (! $item) {
            throw new RuntimeException('Product is not in the cart.');
        }

        if ($quantity > $product->stock) {
            throw new RuntimeException("Requested quantity exceeds available stock ({$product->stock} available).");
        }

        $item->update(['quantity' => $quantity]);

        return $item->load(['product.category', 'product.store']);
    }

    /**
     * Remove a product from the user's cart.
     */
    public function removeItem(User $user, Product $product): void
    {
        $cart = $user->cart;

        if ($cart instanceof Cart) {
            $cart->items()->where('product_id', $product->id)->delete();
        }
    }

    /**
     * Clear all items from the user's cart.
     */
    public function clear(User $user): void
    {
        $cart = $user->cart;

        if ($cart instanceof Cart) {
            $cart->items()->delete();
        }
    }

    /**
     * Sync client-side guest cart items into the authenticated user's cart upon login.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $guestItems
     */
    public function sync(User $user, array $guestItems): Cart
    {
        $cart = $this->getOrCreateCart($user);

        if (empty($guestItems)) {
            return $this->getCart($user);
        }

        $productIds = collect($guestItems)->pluck('product_id')->unique()->all();

        /** @var Collection<int, Product> $products */
        $products = Product::query()
            ->whereIn('id', $productIds)
            ->where('is_published', true)
            ->where('status', ProductStatus::Active)
            ->get()
            ->keyBy('id');

        foreach ($guestItems as $guestItem) {
            $productId = (int) $guestItem['product_id'];
            $quantity = (int) $guestItem['quantity'];

            /** @var Product|null $product */
            $product = $products->get($productId);

            if (! $product || $product->stock <= 0) {
                continue;
            }

            /** @var CartItem|null $existingItem */
            $existingItem = $cart->items()->firstWhere('product_id', $productId);
            $existingQuantity = $existingItem ? (int) $existingItem->quantity : 0;
            $mergedQuantity = $existingQuantity + $quantity;

            if ($mergedQuantity > $product->stock) {
                $mergedQuantity = $product->stock;
            }

            if ($existingItem) {
                $existingItem->update(['quantity' => $mergedQuantity]);
            } else {
                $cart->items()->create([
                    'product_id' => $productId,
                    'quantity' => $mergedQuantity,
                ]);
            }
        }

        return $this->getCart($user);
    }

    private function getOrCreateCart(User $user): Cart
    {
        /** @var Cart $cart */
        $cart = $user->cart()->firstOrCreate();

        return $cart;
    }

    private function assertProductIsPurchasable(Product $product): void
    {
        if (! $product->is_published || $product->status !== ProductStatus::Active) {
            throw new RuntimeException('This product is not currently available for purchase.');
        }

        if ($product->stock <= 0) {
            throw new RuntimeException('This product is out of stock.');
        }
    }
}
