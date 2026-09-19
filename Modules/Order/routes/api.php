<?php

use Illuminate\Support\Facades\Route;
use Modules\Order\Http\Controllers\ArtisanOrderApiController;
use Modules\Order\Http\Controllers\BuyerOrderApiController;
use Modules\Order\Http\Controllers\CartApiController;
use Modules\Order\Http\Controllers\CheckoutApiController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Cart Endpoints
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartApiController::class, 'index']);
        Route::post('/items/{product}', [CartApiController::class, 'store']);
        Route::patch('/items/{product}', [CartApiController::class, 'update']);
        Route::delete('/items/{product}', [CartApiController::class, 'destroy']);
        Route::delete('/', [CartApiController::class, 'clear']);
        Route::post('/sync', [CartApiController::class, 'sync']);
    });

    // Checkout Endpoint
    Route::post('/checkout', CheckoutApiController::class)->middleware('throttle:checkout');

    // Buyer Orders
    Route::prefix('buyer/orders')->group(function () {
        Route::get('/', [BuyerOrderApiController::class, 'index']);
        Route::get('/{order}', [BuyerOrderApiController::class, 'show']);
        Route::post('/{order}/cancel', [BuyerOrderApiController::class, 'cancel']);
    });

    // Artisan Orders
    Route::prefix('artisan/orders')->group(function () {
        Route::get('/', [ArtisanOrderApiController::class, 'index']);
        Route::get('/{order}', [ArtisanOrderApiController::class, 'show']);
        Route::patch('/{order}/status', [ArtisanOrderApiController::class, 'updateStatus']);
    });
});
