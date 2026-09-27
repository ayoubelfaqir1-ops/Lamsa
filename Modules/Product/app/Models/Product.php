<?php

namespace Modules\Product\Models;

use App\Models\Favorite;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\Artisan;
use Modules\Order\Models\OrderItem;
use Modules\Product\Database\Factories\ProductFactory;
use Modules\Product\Enums\ProductStatus;

/**
 * @property int $id
 * @property int $store_id
 * @property int $artisan_id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property float $price
 * @property int $stock
 * @property int|null $quantity
 * @property int|null $total_units_sold
 * @property float|null $reviews_avg_rating
 * @property int|null $reviews_count
 * @property bool $is_published
 * @property ProductStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Category|null $category
 * @property-read Store|null $store
 * @property-read Artisan|null $artisan
 *
 * @method static Builder<static>|Product visible()
 * @method static Builder<static>|Product search(?string $query)
 * @method static Builder<static>|Product inCategory(?int $categoryId)
 * @method static Builder<static>|Product inPriceRange(?float $min, ?float $max)
 *
 * @property array<array-key, mixed>|null $images
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read Collection<int, Review> $reviews
 *
 * @method static \Modules\Product\Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static Builder<static>|Product newModelQuery()
 * @method static Builder<static>|Product newQuery()
 * @method static Builder<static>|Product onlyTrashed()
 * @method static Builder<static>|Product query()
 * @method static Builder<static>|Product whereArtisanId($value)
 * @method static Builder<static>|Product whereCategoryId($value)
 * @method static Builder<static>|Product whereCreatedAt($value)
 * @method static Builder<static>|Product whereDeletedAt($value)
 * @method static Builder<static>|Product whereDescription($value)
 * @method static Builder<static>|Product whereId($value)
 * @method static Builder<static>|Product whereImages($value)
 * @method static Builder<static>|Product whereIsPublished($value)
 * @method static Builder<static>|Product whereName($value)
 * @method static Builder<static>|Product wherePrice($value)
 * @method static Builder<static>|Product whereSlug($value)
 * @method static Builder<static>|Product whereStatus($value)
 * @method static Builder<static>|Product whereStock($value)
 * @method static Builder<static>|Product whereStoreId($value)
 * @method static Builder<static>|Product whereUpdatedAt($value)
 * @method static Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Product withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function newFactory()
    {
        return ProductFactory::new();
    }

    protected $fillable = [
        'store_id', 'artisan_id', 'category_id',
        'name', 'slug', 'description', 'price',
        'stock', 'images', 'status', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'images' => 'array',
            'status' => ProductStatus::class,
            'is_published' => 'boolean',
            'price' => 'decimal:2',
        ];
    }

    // ─── Query Scopes ─────────────────────────────────────────

    /**
     * Only published AND active products — the core visibility rule.
     * SoftDeletes already handles deleted_at via its own global scope.
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query
            ->where('is_published', true)
            ->where('status', ProductStatus::Active);
    }

    /**
     * Search products by name or description using LIKE.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /**
     * Filter products by category slug.
     */
    public function scopeInCategory(Builder $query, string $categorySlug): Builder
    {
        return $query->whereHas('category', function (Builder $q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    /**
     * Filter products within a price range. Either bound can be null (open-ended).
     */
    public function scopeInPriceRange(Builder $query, ?float $min, ?float $max): Builder
    {
        return $query
            ->when($min !== null, fn (Builder $q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn (Builder $q) => $q->where('price', '<=', $max));
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
