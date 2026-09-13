<?php

namespace Modules\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\Artisan;

class ArtisanProfileCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Artisan $artisan) {}
}
