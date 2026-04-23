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

    public const MINIMUM_BID_INCREMENT = 10;

    protected $fillable = [
        'store_id', 'artisan_id', 'category_id',
        'name', 'slug', 'description', 'images',
        'starting_price',
        'reserve_price', 'current_price', 'status',
        'starts_at', 'ends_at', 'is_published',
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
            'images'          => 'array',
            'is_published'    => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', AuctionStatus::Active);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function highestBid(): HasOne
    {
        return $this->hasOne(Bid::class)->ofMany('amount', 'max');
    }

    public function currentBidAmount(): float
    {
        return (float) ($this->current_price ?? $this->starting_price);
    }

    public function minimumNextBid(): float
    {
        return $this->currentBidAmount() + self::MINIMUM_BID_INCREMENT;
    }

    public function canAcceptBids(): bool
    {
        return $this->is_published
            && $this->status === AuctionStatus::Active
            && $this->starts_at?->isPast()
            && $this->ends_at?->isFuture();
    }
}
