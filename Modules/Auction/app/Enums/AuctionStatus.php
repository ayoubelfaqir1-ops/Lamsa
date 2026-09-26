<?php

namespace Modules\Auction\Enums;

enum AuctionStatus: string
{
    case Scheduled = 'scheduled';
    case Active = 'active';
    case Ended = 'ended';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::Active => 'Active',
            self::Ended => 'Ended',
            self::Cancelled => 'Cancelled',
        };
    }
}
