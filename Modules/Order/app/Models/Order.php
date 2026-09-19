<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Order\Database\Factories\OrderFactory;
use Modules\Order\Enums\OrderStatus;

/**
 * @property int $id
 * @property int $user_id
 * @property int $artisan_id
 * @property OrderStatus $status
 * @property float $total_amount
 * @property string $shipping_address
 * @property string|null $payment_method
 * @property string $payment_status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Artisan|null $artisan
 * @property-read Collection<int, OrderItem> $items
 */
class Order extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return OrderFactory::new();
    }

    protected $fillable = [
        'user_id',
        'artisan_id',
        'status',
        'total_amount',
        'shipping_address',
        'payment_method',
        'payment_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'total_amount' => 'decimal:2',
        ];
    }

    /**
     * Scope query to pending orders.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
