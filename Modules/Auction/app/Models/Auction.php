<?php

namespace Modules\Auction\Models;

use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Modules\Auction\Database\Factories\AuctionFactory;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Models\Category;

/**
 * @property int $id
 * @property int $store_id
 * @property int $artisan_id
 * @property int $category_id
 * @property int|null $winner_id
 * @property int|null $winning_bid_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array<string>|null $images
 * @property float $starting_price
 * @property float|null $reserve_price
 * @property float|null $current_price
 * @property AuctionStatus $status
 * @property bool $is_published
 * @property Carbon $starts_at
 * @property Carbon $ends_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Artisan $artisan
 * @property-read Store $store
 * @property-read Category $category
 * @property-read User|null $winner
 * @property-read Bid|null $winningBid
 * @property-read Bid|null $highestBid
 * @property-read Collection<int, Bid> $bids
 * @property-read int|null $bids_count
 *
 * @method static Builder<static>|Auction active()
 * @method static \Modules\Auction\Database\Factories\AuctionFactory factory($count = null, $state = [])
 * @method static Builder<static>|Auction live()
 * @method static Builder<static>|Auction newModelQuery()
 * @method static Builder<static>|Auction newQuery()
 * @method static Builder<static>|Auction published()
 * @method static Builder<static>|Auction query()
 * @method static Builder<static>|Auction whereArtisanId($value)
 * @method static Builder<static>|Auction whereCategoryId($value)
 * @method static Builder<static>|Auction whereCreatedAt($value)
 * @method static Builder<static>|Auction whereCurrentPrice($value)
 * @method static Builder<static>|Auction whereDescription($value)
 * @method static Builder<static>|Auction whereEndsAt($value)
 * @method static Builder<static>|Auction whereId($value)
 * @method static Builder<static>|Auction whereImages($value)
 * @method static Builder<static>|Auction whereIsPublished($value)
 * @method static Builder<static>|Auction whereName($value)
 * @method static Builder<static>|Auction whereReservePrice($value)
 * @method static Builder<static>|Auction whereSlug($value)
 * @method static Builder<static>|Auction whereStartingPrice($value)
 * @method static Builder<static>|Auction whereStartsAt($value)
 * @method static Builder<static>|Auction whereStatus($value)
 * @method static Builder<static>|Auction whereStoreId($value)
 * @method static Builder<static>|Auction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Auction extends Model
{
    use HasFactory;

    public const MINIMUM_BID_INCREMENT = 10;

    protected $fillable = [
        'store_id',
        'artisan_id',
        'category_id',
        'winner_id',
        'winning_bid_id',
        'name',
        'slug',
        'description',
        'images',
        'starting_price',
        'reserve_price',
        'current_price',
        'status',
        'is_published',
        'starts_at',
        'ends_at',
    ];

    protected static function newFactory(): AuctionFactory
    {
        return AuctionFactory::new();
    }

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

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', AuctionStatus::Active);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query
            ->where('status', AuctionStatus::Active)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->where('is_published', true);
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

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function winningBid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'winning_bid_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class)->latest('amount');
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
            && $this->starts_at->isPast()
            && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    public function hasReserveMet(?float $amount = null): bool
    {
        if ($this->reserve_price === null) {
            return true;
        }

        $amountToCheck = $amount ?? $this->currentBidAmount();

        return $amountToCheck >= (float) $this->reserve_price;
    }
}
