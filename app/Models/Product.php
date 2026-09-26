<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
 * @property numeric $price
 * @property int $stock
 * @property array<array-key, mixed>|null $images
 * @property ProductStatus $status
 * @property bool $is_published
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Artisan $artisan
 * @property-read mixed $average_rating
 * @property-read Category $category
 * @property-read Collection<int, Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Store $store
 *
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereArtisanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStoreId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    use HasFactory, SoftDeletes;

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

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function averageRating(): Attribute
    {
        return Attribute::get(
            fn () => $this->reviews()->avg('rating') ?? 0
        );
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
