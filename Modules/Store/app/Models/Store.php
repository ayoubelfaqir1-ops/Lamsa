<?php

namespace Modules\Store\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Auction\Models\Auction;
use Modules\Auth\Models\Artisan;
use Modules\Product\Models\Product;
use Modules\Store\Database\Factories\StoreFactory;

/**
 * @property int $id
 * @property int $artisan_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $logo
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Artisan|null $artisan
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Collection<int, Auction> $auctions
 * @property-read int|null $auctions_count
 *
 * @method static Builder<static>|Store active()
 * @method static StoreFactory factory($count = null, $state = [])
 * @method static Builder<static>|Store newModelQuery()
 * @method static Builder<static>|Store newQuery()
 * @method static Builder<static>|Store query()
 * @method static Builder<static>|Store whereArtisanId($value)
 * @method static Builder<static>|Store whereCreatedAt($value)
 * @method static Builder<static>|Store whereDescription($value)
 * @method static Builder<static>|Store whereId($value)
 * @method static Builder<static>|Store whereIsActive($value)
 * @method static Builder<static>|Store whereLogo($value)
 * @method static Builder<static>|Store whereName($value)
 * @method static Builder<static>|Store whereSlug($value)
 * @method static Builder<static>|Store whereUpdatedAt($value)
 */
class Store extends Model
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    protected $table = 'stores';

    protected $fillable = [
        'artisan_id',
        'name',
        'slug',
        'description',
        'logo',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * @return BelongsTo<Artisan, $this>
     */
    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Auction, $this>
     */
    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    /**
     * Scope query to only include active stores.
     *
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    protected static function newFactory(): StoreFactory
    {
        return StoreFactory::new();
    }
}

