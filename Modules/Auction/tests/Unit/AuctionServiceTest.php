<?php

namespace Modules\Auction\Tests\Unit;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Events\AuctionClosed;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auction\Services\AuctionService;
use Tests\TestCase;

class AuctionServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuctionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->service = new AuctionService;
    }

    public function test_close_single_auction_sets_winner_when_reserve_met(): void
    {
        Event::fake([AuctionClosed::class]);

        // 1. Arrange: Create an expired active auction with a reserve price
        $auction = Auction::factory()->create([
            'status' => AuctionStatus::Active,
            'ends_at' => now()->subMinute(),
            'reserve_price' => 500.00,
        ]);

        // Create bids
        Bid::factory()->for($auction)->create(['amount' => 400.00]);
        $winningBid = Bid::factory()->for($auction)->create(['amount' => 550.00]);

        // 2. Act: Call the service method directly
        $closedAuction = $this->service->closeSingleAuction($auction->id);

        // 3. Assert: Check the results
        $this->assertNotNull($closedAuction);
        $this->assertEquals(AuctionStatus::Ended, $closedAuction->status);
        $this->assertEquals($winningBid->user_id, $closedAuction->winner_id);
        $this->assertEquals($winningBid->id, $closedAuction->winning_bid_id);
        $this->assertFalse($closedAuction->is_published);

        Event::assertDispatched(AuctionClosed::class, function ($event) use ($auction) {
            return $event->auction->id === $auction->id;
        });
    }

    public function test_close_single_auction_has_no_winner_when_reserve_not_met(): void
    {
        Event::fake([AuctionClosed::class]);

        $auction = Auction::factory()->create([
            'status' => AuctionStatus::Active,
            'ends_at' => now()->subMinute(),
            'reserve_price' => 600.00,
        ]);

        Bid::factory()->for($auction)->create(['amount' => 550.00]); // Bid below reserve

        $closedAuction = $this->service->closeSingleAuction($auction->id);

        $this->assertNotNull($closedAuction);
        $this->assertEquals(AuctionStatus::Ended, $closedAuction->status);
        $this->assertNull($closedAuction->winner_id); // No winner
        $this->assertNull($closedAuction->winning_bid_id);
    }
}
