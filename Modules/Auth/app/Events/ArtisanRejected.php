<?php

namespace Modules\Auth\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\Artisan;

class ArtisanRejected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Artisan $artisan,
        public ?string $reason = null
    ) {}
}

