<?php

use Illuminate\Support\Facades\Route;
use Modules\Store\Http\Controllers\ArtisanStoreApiController;
use Modules\Store\Http\Controllers\PublicStoreApiController;

// Public Storefront
Route::get('/stores/{slug}', [PublicStoreApiController::class, 'show']);

// Artisan Store Management
Route::middleware(['auth:sanctum', 'role:artisan'])->prefix('artisan/store')->group(function () {
    Route::get('/', [ArtisanStoreApiController::class, 'show']);
    Route::post('/', [ArtisanStoreApiController::class, 'store']);
    Route::match(['put', 'patch', 'post'], '/update', [ArtisanStoreApiController::class, 'update']);
    Route::delete('/logo', [ArtisanStoreApiController::class, 'destroyLogo']);
});

