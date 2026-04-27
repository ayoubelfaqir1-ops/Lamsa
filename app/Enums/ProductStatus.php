<?php

namespace App\Enums;

enum ProductStatus: string
{
    case Pending   = 'pending';
    case Inactive    = 'inactive';
    case Active    = 'active';
    case Suspended  = 'suspended';
}
