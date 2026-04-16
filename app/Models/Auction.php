<?php

namespace App\Models;

use App\Enums\AuctionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Auction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'artisan_id', 'starting_price',
        'reserve_price', 'current_price', 'status',
        'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'status'          => AuctionStatus::class,
            'starting_price'  => 'decimal:2',
            'reserve_price'   => 'decimal:2',
            'current_price'   => 'decimal:2',
            'starts_at'       => 'datetime',
            'ends_at'         => 'datetime',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', AuctionStatus::Active);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function highestBid(): HasOne
    {
        return $this->hasOne(Bid::class)->ofMany('amount', 'max');
    }
}
