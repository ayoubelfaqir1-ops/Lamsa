<?php

use Illuminate\Support\Facades\Route;
use Modules\Auction\Http\Controllers\ArtisanAuctionApiController;
use Modules\Auction\Http\Controllers\BuyerBidApiController;
use Modules\Auction\Http\Controllers\PublicAuctionApiController;

/*
|--------------------------------------------------------------------------
| Auction Module API Routes
|--------------------------------------------------------------------------
|
| Base prefix: /api/v1
|
*/

// Public Auction Catalog
Route::get('/auctions', [PublicAuctionApiController::class, 'index']);
Route::get('/auctions/{idOrSlug}', [PublicAuctionApiController::class, 'show']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // Buyer Bidding
    Route::post('/auctions/{auction}/bids', [BuyerBidApiController::class, 'store'])
        ->middleware('throttle:bidding');
    Route::get('/buyer/bids', [BuyerBidApiController::class, 'myBids']);
    Route::delete('/buyer/bids/{bid}', [BuyerBidApiController::class, 'destroy']);

    // Artisan Auction Management
    Route::prefix('artisan/auctions')->group(function () {
        Route::get('/', [ArtisanAuctionApiController::class, 'index']);
        Route::post('/', [ArtisanAuctionApiController::class, 'store']);
        Route::patch('/{auction}', [ArtisanAuctionApiController::class, 'update']);
        Route::delete('/{auction}/cancel', [ArtisanAuctionApiController::class, 'cancel']);
    });
});
