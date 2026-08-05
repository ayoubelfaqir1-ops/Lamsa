<?php

use App\Http\Controllers\Admin\ArtisanController as AdminArtisanController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Artisan\AuctionController as ArtisanAuctionController;
use App\Http\Controllers\Artisan\BidController as ArtisanBidController;
use App\Http\Controllers\Artisan\DashboardController as ArtisanDashboardController;
use App\Http\Controllers\Artisan\OrderController as ArtisanOrderController;
use App\Http\Controllers\Artisan\ProductController as ArtisanProductController;
use App\Http\Controllers\Artisan\StoreController as ArtisanStoreController;
use App\Http\Controllers\Buyer\AuctionController as BuyerAuctionController;
use App\Http\Controllers\Buyer\BidController as BuyerBidController;
use App\Http\Controllers\Buyer\CartController as BuyerCartController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\ProductController as BuyerProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Buyer\StoreController as BuyerStoreController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::view('/mission', 'mission')->name('mission');
Route::get('/auctions', [BuyerAuctionController::class, 'index'])->name('auctions.index');
Route::get('/auctions/{auction}', [BuyerAuctionController::class, 'show'])->name('auctions.show');
Route::get('/products', [BuyerProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [BuyerProductController::class, 'show'])->name('products.show');
Route::get('/stores', [BuyerStoreController::class, 'index'])->name('stores.index');
Route::get('/stores/{store:slug}', [BuyerStoreController::class, 'show'])->name('stores.show');

// Cart Routes
Route::get('/cart', [BuyerCartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [BuyerCartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{product}', [BuyerCartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{product}', [BuyerCartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/clear', [BuyerCartController::class, 'clear'])->name('cart.clear');

Route::middleware(['auth', 'profile.complete'])->group(function () {
    Route::get('/my-orders', [BuyerOrderController::class, 'index'])->name('orders.index');
    Route::get('/my-orders/{order}', [BuyerOrderController::class, 'show'])->name('orders.show');
    Route::get('/checkout/payment/card', [BuyerOrderController::class, 'cardPayment'])->name('orders.payment.card');
    Route::post('/checkout/payment/card', [BuyerOrderController::class, 'processCardPayment'])->name('orders.payment.card.process');
    Route::get('/checkout', [BuyerOrderController::class, 'create'])->name('orders.create');
    Route::post('/checkout', [BuyerOrderController::class, 'store'])->name('orders.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/my-bids', [BuyerBidController::class, 'myBids'])->name('bids.my');
    Route::post('/auctions/{auction}/bids', [BuyerBidController::class, 'store'])->name('bids.store');
    Route::delete('/bids/{bid}', [BuyerBidController::class, 'destroy'])->name('bids.destroy');
});

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

    Route::get('artisan/auctions', [ArtisanAuctionController::class, 'index'])
        ->name('artisan.auctions');

    Route::get('artisan/bids', [ArtisanBidController::class, 'index'])
        ->name('artisan.bids');

    Route::get('artisan/auctions/create', [ArtisanAuctionController::class, 'create'])
        ->name('artisan.auctions.create');

    Route::post('artisan/auctions', [ArtisanAuctionController::class, 'store'])
        ->name('artisan.auctions.store');

    Route::get('artisan/auctions/{auction}/edit', [ArtisanAuctionController::class, 'edit'])
        ->name('artisan.auctions.edit');

    Route::patch('artisan/auctions/{auction}', [ArtisanAuctionController::class, 'update'])
        ->name('artisan.auctions.update');

    Route::patch('artisan/auctions/{auction}/toggle-publish', [ArtisanAuctionController::class, 'togglePublish'])
        ->name('artisan.auctions.toggle-publish');

    Route::patch('artisan/auctions/{auction}/cancel', [ArtisanAuctionController::class, 'cancel'])
        ->name('artisan.auctions.cancel');

    Route::delete('artisan/auctions/{auction}', [ArtisanAuctionController::class, 'destroy'])
        ->name('artisan.auctions.destroy');

    Route::get('artisan/store/create', [ArtisanStoreController::class, 'create'])
        ->name('artisan.store.create');

    Route::post('artisan/store', [ArtisanStoreController::class, 'store'])
        ->name('artisan.store.store');

    Route::get('artisan/store/{store}', [ArtisanStoreController::class, 'show'])
        ->name('artisan.store.show');

    Route::get('artisan/store/{store}/edit', [ArtisanStoreController::class, 'edit'])
        ->name('artisan.store.edit');

    Route::patch('artisan/store/{store}', [ArtisanStoreController::class, 'update'])
        ->name('artisan.store.update');

    Route::resource('artisan/orders', ArtisanOrderController::class)
        ->only(['index', 'show'])
        ->names([
            'index' => 'artisan.orders',
            'show' => 'artisan.orders.show',
        ]);
    Route::patch('artisan/orders/{order}/status', [ArtisanOrderController::class, 'updateStatus'])
        ->name('artisan.orders.status');

    Route::get('artisan/products', [ArtisanProductController::class, 'index'])
        ->name('artisan.products');

    Route::get('artisan/products/create', [ArtisanProductController::class, 'create'])
        ->name('artisan.products.create');

    Route::post('artisan/products', [ArtisanProductController::class, 'store'])
        ->name('artisan.products.store');

    Route::get('artisan/products/{product}/edit', [ArtisanProductController::class, 'edit'])
        ->name('artisan.products.edit');

    Route::patch('artisan/products/{product}', [ArtisanProductController::class, 'update'])
        ->name('artisan.products.update');

    Route::patch('artisan/products/{product}/toggle-publish', [ArtisanProductController::class, 'togglePublish'])
        ->name('artisan.products.toggle-publish');

    Route::delete('artisan/products/{product}', [ArtisanProductController::class, 'destroy'])
        ->name('artisan.products.destroy');
});

Route::get('profile', [ProfileController::class, 'show'])
    ->middleware(['auth'])
    ->name('profile');
