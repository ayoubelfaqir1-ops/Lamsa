<?php

namespace Modules\Auction\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Auction\Database\Factories\BidFactory;
use Modules\Auth\Models\User;

/**
 * @property int $id
 * @property int $auction_id
 * @property int $user_id
 * @property float $amount
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read Auction $auction
 * @property-read User $user
 */
class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'auction_id',
        'user_id',
        'amount',
    ];

    protected static function newFactory(): BidFactory
    {
        return BidFactory::new();
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
