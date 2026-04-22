<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'artisan_id','status', 'total_amount',
        'shipping_address', 'payment_method',
        'payment_status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'status'       => OrderStatus::class,
            'total_amount' => 'decimal:2',
        ];
    }

    public function scopePending($query)
    {
        return $query->where('status', OrderStatus::Pending);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
