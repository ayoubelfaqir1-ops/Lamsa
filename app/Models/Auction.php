<?php

namespace App\Models;

use App\Enums\AuctionStatus;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\Artisan;

/**
 * @property int $id
 * @property int $store_id
 * @property int $artisan_id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<array-key, mixed>|null $images
 * @property numeric $starting_price
 * @property numeric|null $reserve_price
 * @property numeric|null $current_price
 * @property AuctionStatus $status
 * @property bool $is_published
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Artisan $artisan
 * @property-read Collection<int, Bid> $bids
 * @property-read int|null $bids_count
 * @property-read Category $category
 * @property-read Bid|null $highestBid
 * @property-read Store $store
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction active()
 * @method static \Database\Factories\AuctionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereArtisanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereCurrentPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereReservePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStartingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Auction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
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
            'status' => AuctionStatus::class,
            'starting_price' => 'decimal:2',
            'reserve_price' => 'decimal:2',
            'current_price' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'images' => 'array',
            'is_published' => 'boolean',
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
