<?php

use App\Http\Controllers\admin\ArtisanController as AdminArtisanController;
use App\Http\Controllers\admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Artisan\DashboardController as ArtisanDashboardController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

// Generic dashboard redirector
Route::get('dashboard', DashboardController::class)
    ->middleware(['auth'])
    ->name('dashboard');

// Admin Routes
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');

    Route::get('admin/artisans', [AdminArtisanController::class, 'index'])
        ->name('admin.artisans');

    Route::get('admin/categories', [AdminCategoryController::class, 'index'])
        ->name('admin.categories');

    Route::get('admin/categories/create', [AdminCategoryController::class, 'create'])
        ->name('admin.categories.create');

    Route::post('admin/categories', [AdminCategoryController::class, 'store'])
        ->name('admin.categories.store');

    Route::get('admin/categories/{category}', [AdminCategoryController::class, 'show'])
        ->name('admin.categories.show');

    Route::patch('admin/categories/{category}', [AdminCategoryController::class, 'update'])
        ->name('admin.categories.update');

    Route::patch('admin/categories/{category}/toggle-active', [AdminCategoryController::class, 'toggleActive'])
        ->name('admin.categories.toggle-active');

    Route::delete('admin/categories/{category}', [AdminCategoryController::class, 'destroy'])
        ->name('admin.categories.destroy');

});

// Artisan Routes
Route::middleware(['auth', 'role:artisan', 'artisan.active'])->group(function () {
    Route::get('artisan/dashboard', [ArtisanDashboardController::class, 'index'])
        ->name('artisan.dashboard');

    Route::get('artisan/products', function () {
        return 'Artisan Product Management In Development';
    });
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
