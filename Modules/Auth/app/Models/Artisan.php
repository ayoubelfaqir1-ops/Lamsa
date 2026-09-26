<?php

namespace Modules\Auth\Models;

use App\Enums\ArtisanStatus;
use App\Models\Auction;
use App\Models\Order;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Modules\Auth\Database\Factories\ArtisanFactory;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $bio
 * @property string|null $city
 * @property string|null $region
 * @property ArtisanStatus $status
 * @property string|null $craft_type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Auction> $auctions
 * @property-read int|null $auctions_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, Product> $products
 * @property-read int|null $products_count
 * @property-read Store|null $store
 * @property-read User $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan active()
 * @method static \Modules\Auth\Database\Factories\ArtisanFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereCraftType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Artisan whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Artisan extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return ArtisanFactory::new();
    }

    protected $fillable = [
        'user_id', 'bio', 'city', 'region',
        'craft_type', 'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArtisanStatus::class,
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', ArtisanStatus::Active);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function store(): HasOne
    {
        return $this->hasOne(Store::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function auctions(): HasMany
    {
        return $this->hasMany(Auction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
