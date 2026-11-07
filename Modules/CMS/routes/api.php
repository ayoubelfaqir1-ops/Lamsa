<?php

use Illuminate\Support\Facades\Route;
use Modules\CMS\Http\Controllers\Admin\PageController as AdminPageController;
use Modules\CMS\Http\Controllers\PageController;

/*
|--------------------------------------------------------------------------
| Public CMS Routes
|--------------------------------------------------------------------------
*/
Route::prefix('pages')->group(function () {
    Route::get('/', [PageController::class, 'index']);
    Route::get('/{slug}', [PageController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Admin CMS Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('pages', AdminPageController::class);
});
