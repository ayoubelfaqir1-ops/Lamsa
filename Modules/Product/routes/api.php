<?php

use Illuminate\Support\Facades\Route;
use Modules\Product\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use Modules\Product\Http\Controllers\AdminProductController;
use Modules\Product\Http\Controllers\ArtisanProductController;
use Modules\Product\Http\Controllers\CategoryController;
use Modules\Product\Http\Controllers\FavoriteController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::match(['post', 'delete'], '/products/{id}/favorite', [FavoriteController::class, 'toggle']);
    Route::get('/favorites', [FavoriteController::class, 'index']);
});

Route::middleware(['auth:sanctum', 'role:artisan'])->prefix('artisan')->group(function () {
    Route::apiResource('products', ArtisanProductController::class);
    Route::patch('products/{product}/toggle-publish', [ArtisanProductController::class, 'togglePublish']);
});

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('categories', AdminCategoryController::class);
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::patch('/products/{product}', [AdminProductController::class, 'update']);
    Route::delete('/products/{product}', [AdminProductController::class, 'destroy']);
    Route::post('/products/{id}/restore', [AdminProductController::class, 'restore']);
});
