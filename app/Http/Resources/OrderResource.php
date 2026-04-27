<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status->value,
            'total_amount'     => $this->total_amount,
            'shipping_address' => $this->shipping_address,
            'payment_method'   => $this->payment_method,
            'payment_status'   => $this->payment_status,
            'notes'            => $this->notes,
            'items'            => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'id'         => $item->id,
                    'product'    => [
                        'id'   => $item->product?->id,
                        'name' => $item->product?->name,
                        'slug' => $item->product?->slug,
                    ],
                    'artisan'    => [
                        'id'   => $item->artisan?->id,
                        'name' => $item->artisan?->user?->name,
                    ],
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal'   => $item->subtotal,
                ])
            ),
            'created_at'       => $this->created_at,
        ];
    }
}
