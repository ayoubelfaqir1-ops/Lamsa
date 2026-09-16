<?php

namespace Modules\Product\Models;

use Modules\Product\Enums\ProductStatus;
use App\Models\Favorite;
use App\Models\OrderItem;
use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Auth\Models\Artisan;
use Modules\Product\Database\Factories\ProductFactory;

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
