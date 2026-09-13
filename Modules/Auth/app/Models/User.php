<?php

namespace Modules\Auth\Models;

use App\Models\Bid;
use App\Models\Cart;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\Review;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Modules\Auth\Notifications\VerifyEmailNotification;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasRoles, HasFactory, Notifiable;

    protected static function newFactory()
    {
        return \Modules\Auth\Database\Factories\UserFactory::new();
    }

    protected $fillable = ['name', 'email', 'password', 'phone', 'address', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * Send the queued email verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isArtisan(): bool
    {
        return $this->hasRole('artisan');
    }

    public function isBuyer(): bool
    {
        return $this->hasRole('buyer');
    }

    public function hasCompleteCheckoutProfile(): bool
    {
        return trim((string) $this->phone) !== ''
            && trim((string) $this->address) !== '';
    }

    public function artisan(): HasOne
    {
        return $this->hasOne(Artisan::class);
    }

    public function store(): HasOneThrough
    {
        return $this->hasOneThrough(Store::class, Artisan::class, 'user_id', 'artisan_id', 'id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }
}
