<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Order\Models\CartItem;

/**
 * @mixin CartItem
 */
class CartItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'product_slug' => $this->product?->slug,
            'unit_price' => $this->product ? (float) $this->product->price : 0.0,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'stock_available' => $this->product?->stock,
            'is_available' => $this->isAvailable(),
            'store' => $this->product?->store ? [
                'id' => $this->product->store->id,
                'name' => $this->product->store->name,
            ] : null,
        ];
    }
}
