<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Auth\Models\User;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| Auction module supports. The given channel authorization callbacks are
| used to verify if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('auction.{id}', function (User $user, int $id): bool {
    // Any authenticated user with a valid Sanctum token is authorized to listen to real-time bids
    return true;
}, ['guards' => ['sanctum']]);
