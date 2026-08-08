<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This application operates as a pure REST API Backend.
| All feature routes are defined in domain modules under /api/v1/*.
|
*/

Route::get('/', function () {
    return response()->json([
        'name'    => config('app.name', 'Lamsa API'),
        'version' => '1.0.0',
        'status'  => 'healthy',
        'docs'    => url('/api/v1/auth/auth-test'),
    ]);
});
