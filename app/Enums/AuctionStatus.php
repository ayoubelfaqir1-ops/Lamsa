<?php

namespace App\Enums;

enum AuctionStatus: string
{
    case Active    = 'active';
    case Ended     = 'ended';
    case Cancelled = 'cancelled';
}
