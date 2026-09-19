<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\Artisan;
use Modules\Order\Database\Factories\OrderItemFactory;
use Modules\Product\Models\Product;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property int $artisan_id
 * @property string $product_name
 * @property int $quantity
 * @property float $unit_price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Order|null $order
 * @property-read Product|null $product
 * @property-read Artisan|null $artisan
 * @property-read float $subtotal
 */
class OrderItem extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return OrderItemFactory::new();
    }

    protected $fillable = [
        'order_id',
        'product_id',
        'artisan_id',
        'product_name',
        'quantity',
        'unit_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function getSubtotalAttribute(): float
    {
        return (float) ($this->quantity * $this->unit_price);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }
}
