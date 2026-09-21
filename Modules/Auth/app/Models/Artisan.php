<?php

namespace Modules\Auth\Models;

use App\Enums\ArtisanStatus;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Auction\Models\Auction;
use Modules\Auth\Database\Factories\ArtisanFactory;
use Modules\Order\Models\Order;
use Modules\Product\Models\Product;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $bio
 * @property string|null $city
 * @property string|null $region
 * @property string|null $craft_type
 * @property ArtisanStatus $status
 * @property-read User|null $user
 * @property-read Store|null $store
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

    /**
     * @return BelongsTo<User, $this>
     */
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
