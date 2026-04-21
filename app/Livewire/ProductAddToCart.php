<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Locked;
use Livewire\Component;
use RuntimeException;

class ProductAddToCart extends Component
{
    #[Locked]
    public Product $product;

    public int $quantity = 1;

    public ?string $message = null;

    public string $messageType = 'success';

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->quantity = $this->hasStock ? 1 : 0;
    }

    public function getHasStockProperty(): bool
    {
        return $this->product->stock > 0;
    }

    public function getAvailabilityNoteProperty(): string
    {
        if (! $this->hasStock) {
            return 'Available as a made-to-order piece';
        }

        return $this->product->stock . ' piece' . ($this->product->stock === 1 ? '' : 's') . ' currently available';
    }

    public function decrementQuantity(): void
    {
        if (! $this->hasStock) {
            return;
        }

        $this->quantity = max(1, $this->quantity - 1);
    }

    public function incrementQuantity(): void
    {
        if (! $this->hasStock) {
            return;
        }

        $this->quantity = min($this->product->stock, $this->quantity + 1);
    }

    public function updatedQuantity($value): void
    {
        if (! $this->hasStock) {
            $this->quantity = 0;

            return;
        }

        $quantity = (int) $value;

        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($quantity > $this->product->stock) {
            $quantity = $this->product->stock;
        }

        $this->quantity = $quantity;
    }

    public function addToCart(CartService $cartService): void
    {
        $this->message = null;

        try {
            $cartService->add($this->product, $this->quantity);
            $this->message = 'Product added to cart.';
            $this->messageType = 'success';
        } catch (RuntimeException $exception) {
            $this->message = $exception->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.product-add-to-cart');
    }
}
