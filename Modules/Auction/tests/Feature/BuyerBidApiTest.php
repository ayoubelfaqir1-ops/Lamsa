<?php

namespace Modules\Auction\Tests\Feature;

use App\Enums\ArtisanStatus;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Events\BidPlaced;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Models\Category;
use Tests\TestCase;

class BuyerBidApiTest extends TestCase
{
    use RefreshDatabase;

    private User $artisanUser;

    private Artisan $artisan;

    private Auction $auction;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisanUser = User::factory()->create();
        $this->artisan = Artisan::factory()->create([
            'user_id' => $this->artisanUser->id,
            'status' => ArtisanStatus::Active,
        ]);
        Store::factory()->create(['artisan_id' => $this->artisan->id]);
        $this->artisanUser->assignRole('artisan');

        $category = Category::factory()->create(['is_active' => true]);

        $this->auction = Auction::factory()->create([
            'artisan_id' => $this->artisan->id,
            'category_id' => $category->id,
            'starting_price' => 100.00,
            'current_price' => 100.00,
            'status' => AuctionStatus::Active,
            'is_published' => true,
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHours(5),
        ]);

        $this->buyer = User::factory()->buyer()->create();
    }

    public function test_unauthenticated_user_cannot_bid(): void
    {
        $response = $this->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
            'amount' => 150.00,
        ]);

        $response->assertStatus(401);
    }

    public function test_artisan_cannot_bid_on_their_own_auction(): void
    {
        $response = $this->actingAs($this->artisanUser)
            ->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
                'amount' => 150.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'You cannot bid on your own auction.');
    }

    public function test_buyer_can_place_valid_bid_and_event_is_broadcasted(): void
    {
        Event::fake([BidPlaced::class]);

        $response = $this->actingAs($this->buyer)
            ->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
                'amount' => 150.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Bid placed successfully.')
            ->assertJsonPath('data.amount', 150)
            ->assertJsonPath('data.auction_id', $this->auction->id);

        $this->assertDatabaseHas('bids', [
            'auction_id' => $this->auction->id,
            'user_id' => $this->buyer->id,
            'amount' => 150.00,
        ]);

        $this->assertDatabaseHas('auctions', [
            'id' => $this->auction->id,
            'current_price' => 150.00,
        ]);

        Event::assertDispatched(BidPlaced::class, function (BidPlaced $event) {
            return $event->bid->auction_id === $this->auction->id
                && (float) $event->bid->amount === 150.0;
        });
    }

    public function test_underbid_is_rejected_and_no_broadcast_fired(): void
    {
        Event::fake([BidPlaced::class]);

        // Existing bid of 150
        Bid::factory()->for($this->auction)->create(['amount' => 150.00]);
        $this->auction->update(['current_price' => 150.00]);

        // Attempt lower bid of 90
        $response = $this->actingAs($this->buyer)
            ->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
                'amount' => 90.00,
            ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('bids', [
            'auction_id' => $this->auction->id,
            'amount' => 90.00,
        ]);

        Event::assertNotDispatched(BidPlaced::class);
    }

    public function test_bid_equal_to_current_price_is_rejected(): void
    {
        Bid::factory()->for($this->auction)->create(['amount' => 120.00]);
        $this->auction->update(['current_price' => 120.00]);

        $response = $this->actingAs($this->buyer)
            ->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
                'amount' => 120.00,
            ]);

        $response->assertStatus(422);
    }

    public function test_bid_on_expired_auction_is_rejected_regardless_of_amount(): void
    {
        $this->auction->update([
            'ends_at' => now()->subMinute(),
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
                'amount' => 500.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'This auction has already ended.');
    }

    public function test_bid_on_inactive_or_cancelled_auction_is_rejected(): void
    {
        $this->auction->update([
            'status' => AuctionStatus::Cancelled,
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
                'amount' => 500.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'This auction is not active.');
    }

    public function test_bid_on_scheduled_future_auction_is_rejected(): void
    {
        $this->auction->update([
            'status' => AuctionStatus::Active,
            'starts_at' => now()->addDays(2),
        ]);

        $response = $this->actingAs($this->buyer)
            ->postJson("/api/v1/auctions/{$this->auction->id}/bids", [
                'amount' => 500.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'This auction has not started yet.');
    }

    public function test_buyer_can_view_their_bids_dashboard(): void
    {
        Bid::factory()->for($this->auction)->for($this->buyer)->create(['amount' => 120.00]);

        $response = $this->actingAs($this->buyer)
            ->getJson('/api/v1/buyer/bids');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.amount', 120);
    }
}
