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
 *
 * @method static \Modules\Auction\Database\Factories\BidFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereAuctionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Bid whereUserId($value)
 *
 * @mixin \Eloquent
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
