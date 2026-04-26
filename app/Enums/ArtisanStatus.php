<?php

namespace App\Enums;

enum ArtisanStatus: string
{
    case Pending   = 'pending';
    case Active    = 'active';
    case Suspended = 'suspended';
    case Rejected  = 'rejected';
}
