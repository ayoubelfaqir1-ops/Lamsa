<?php

namespace Modules\Order\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Modules\Order\Models\Cart;
use Modules\Order\Models\CartItem;

/**
 * @mixin Cart
 */
class CartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Collection<int, CartItem> $items */
        $items = $this->items ?? collect();

        $availableItems = $items->filter(fn (CartItem $item) => $item->isAvailable());

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items' => CartItemResource::collection($items),
            'total_items' => (int) $items->sum('quantity'),
            'total_price' => (float) $availableItems->sum(fn (CartItem $item) => $item->subtotal),
            'has_unavailable_items' => $items->count() !== $availableItems->count(),
        ];
    }
}
