<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\AdminArtisanApiController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/artisans', [AdminArtisanApiController::class, 'index']);
    Route::patch('/artisans/{artisan}/status', [AdminArtisanApiController::class, 'updateStatus']);
});
