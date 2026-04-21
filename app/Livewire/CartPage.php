<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use RuntimeException;

class CartPage extends Component
{
    public ?string $message = null;

    public string $messageType = 'success';

    public function increment(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $currentItem = $this->getCartService()->getDisplayItems()->firstWhere('id', $productId);

        if (! $currentItem) {
            $this->setMessage('Product not in cart.', 'error');

            return;
        }

        $this->updateQuantity($product, $currentItem->quantity + 1);
    }

    public function decrement(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $currentItem = $this->getCartService()->getDisplayItems()->firstWhere('id', $productId);

        if (! $currentItem) {
            $this->setMessage('Product not in cart.', 'error');

            return;
        }

        if ($currentItem->quantity <= 1) {
            return;
        }

        $this->updateQuantity($product, $currentItem->quantity - 1);
    }

    public function remove(int $productId): void
    {
        $product = Product::findOrFail($productId);

        $this->getCartService()->remove($product);
        $this->setMessage('Product removed from cart.');
    }

    public function clear(): void
    {
        $this->getCartService()->clear();
        $this->setMessage('Cart cleared.');
    }

    public function render()
    {
        $cart = $this->getCartService()->getDisplayItems();
        $total = $cart->sum(fn (Product $item) => $item->price * $item->quantity);

        return view('livewire.cart-page', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    private function updateQuantity(Product $product, int $quantity): void
    {
        try {
            $this->getCartService()->update($product, $quantity);
            $this->setMessage('Cart updated.');
        } catch (RuntimeException $exception) {
            $this->setMessage($exception->getMessage(), 'error');
        }
    }

    private function getCartService(): CartService
    {
        return app(CartService::class);
    }

    private function setMessage(string $message, string $type = 'success'): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
