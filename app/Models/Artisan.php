<?php

namespace App\Models;

use App\Enums\ArtisanStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Artisan extends Model
{
    use HasFactory;

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
