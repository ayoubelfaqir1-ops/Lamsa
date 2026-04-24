<?php

use App\Models\Artisan;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Store;
use App\Models\User;

function createLiveAuctionForArtisan(): array
{
    $artisan = Artisan::factory()->create();
    $store = Store::factory()->create([
        'artisan_id' => $artisan->id,
    ]);

    $auction = Auction::factory()->create([
        'artisan_id' => $artisan->id,
        'store_id' => $store->id,
        'starting_price' => 100,
        'current_price' => 100,
        'is_published' => true,
        'starts_at' => now()->subHour(),
        'ends_at' => now()->addHour(),
    ]);

    return [$artisan, $store, $auction];
}

test('guests are redirected to login when trying to place a bid', function () {
    [, , $auction] = createLiveAuctionForArtisan();

    $response = $this->post(route('bids.store', $auction), [
        'amount' => $auction->minimumNextBid(),
    ]);

    $response->assertRedirect(route('login'));
});

test('buyers can place bids on live auctions', function () {
    [, , $auction] = createLiveAuctionForArtisan();
    $buyer = User::factory()->buyer()->create();

    $response = $this->actingAs($buyer)->post(route('bids.store', $auction), [
        'amount' => 150,
    ]);

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Your bid has been placed successfully.');

    $this->assertDatabaseHas('bids', [
        'auction_id' => $auction->id,
        'user_id' => $buyer->id,
        'amount' => 150,
    ]);

    expect($auction->fresh()->current_price)->toBe('150.00');
});

test('non buyer users cannot place bids', function () {
    [$artisan, , $auction] = createLiveAuctionForArtisan();

    $response = $this->actingAs($artisan->user)->post(route('bids.store', $auction), [
        'amount' => 150,
    ]);

    $response->assertForbidden();
});

test('buyers can review their own bids', function () {
    [, , $auction] = createLiveAuctionForArtisan();
    $buyer = User::factory()->buyer()->create();

    Bid::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $buyer->id,
        'amount' => 150,
    ]);

    $response = $this->actingAs($buyer)->get(route('bids.my'));

    $response
        ->assertOk()
        ->assertSee('My Bids')
        ->assertSee($auction->name)
        ->assertSee('150.00');
});

test('buyers can withdraw their own bids while auction is still live', function () {
    [, , $auction] = createLiveAuctionForArtisan();
    $buyer = User::factory()->buyer()->create();

    $bid = Bid::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $buyer->id,
        'amount' => 150,
    ]);

    $auction->update(['current_price' => 150]);

    $response = $this->actingAs($buyer)->delete(route('bids.destroy', $bid));

    $response
        ->assertRedirect()
        ->assertSessionHas('success', 'Your bid has been withdrawn.');

    $this->assertDatabaseMissing('bids', [
        'id' => $bid->id,
    ]);

    expect($auction->fresh()->current_price)->toBe('100.00');
});

test('artisans can see bids placed on their auctions', function () {
    [$artisan, , $auction] = createLiveAuctionForArtisan();
    $buyer = User::factory()->buyer()->create([
        'name' => 'Bid Buyer',
    ]);

    Bid::factory()->create([
        'auction_id' => $auction->id,
        'user_id' => $buyer->id,
        'amount' => 180,
    ]);

    $response = $this->actingAs($artisan->user)->get(route('artisan.bids'));

    $response
        ->assertOk()
        ->assertSee('Received Bids')
        ->assertSee($auction->name)
        ->assertSee('Bid Buyer')
        ->assertSee('180.00');
});
