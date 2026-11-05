<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\ArtisanProductController;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Http\Controllers\ProductCatalogController;
use Modules\Product\Http\Controllers\ReviewController;

Route::middleware('throttle:catalog')->group(function () {
    Route::get('/products', [ProductCatalogController::class, 'index']);
    Route::get('/products/{slug}', [ProductCatalogController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products/{id}/reviews', [ReviewController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'role:artisan'])->prefix('artisan')->group(function () {
    Route::apiResource('products', ArtisanProductController::class);
    Route::patch('products/{product}/toggle-publish', [ArtisanProductController::class, 'togglePublish']);
});
