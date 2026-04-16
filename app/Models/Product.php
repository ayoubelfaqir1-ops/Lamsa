<?php

namespace App\Models;

use App\Enums\ProductMode;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id', 'artisan_id', 'category_id',
        'name', 'slug', 'description', 'price',
        'stock', 'images', 'mode', 'status', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'images'       => 'array',
            'mode'         => ProductMode::class,
            'status'       => ProductStatus::class,
            'is_published' => 'boolean',
            'price'        => 'decimal:2',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeDirect($query)
    {
        return $query->where('mode', ProductMode::Direct);
    }

    public function scopeAuction($query)
    {
        return $query->where('mode', ProductMode::Auction);
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

    public function auction(): HasOne
    {
        return $this->hasOne(Auction::class);
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
