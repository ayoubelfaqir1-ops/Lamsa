<?php

namespace Modules\Auction\Tests\Feature;

use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Events\AuctionClosed;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auction\Services\AuctionService;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Models\Category;
use Tests\TestCase;

class CloseExpiredAuctionsTest extends TestCase
{
    use RefreshDatabase;

    private AuctionService $auctionService;

    private Artisan $artisan;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->auctionService = app(AuctionService::class);

        $user = User::factory()->create();
        $this->artisan = Artisan::factory()->create(['user_id' => $user->id]);
        Store::factory()->create(['artisan_id' => $this->artisan->id]);

        $this->category = Category::factory()->create(['is_active' => true]);
    }

    public function test_happy_path_winner_determined_and_broadcast(): void
    {
        Event::fake([AuctionClosed::class]);

        $auction = Auction::factory()->for($this->artisan)->create([
            'category_id' => $this->category->id,
            'starting_price' => 100.00,
            'current_price' => 200.00,
            'reserve_price' => 150.00,
            'status' => AuctionStatus::Active,
            'is_published' => true,
            'starts_at' => now()->subDays(3),
            'ends_at' => now()->subHour(), // Expired
        ]);

        $buyer1 = User::factory()->buyer()->create();
        $buyer2 = User::factory()->buyer()->create();

        Bid::factory()->for($auction)->for($buyer1)->create(['amount' => 160.00]);
        $winningBid = Bid::factory()->for($auction)->for($buyer2)->create(['amount' => 200.00]);

        // Run the closeExpired sweeper
        $closed = $this->auctionService->closeExpired();

        $this->assertCount(1, $closed);

        $auction->refresh();
        $this->assertEquals(AuctionStatus::Ended, $auction->status);
        $this->assertFalse($auction->is_published);
        $this->assertEquals($buyer2->id, $auction->winner_id);
        $this->assertEquals($winningBid->id, $auction->winning_bid_id);

        Event::assertDispatched(AuctionClosed::class, function (AuctionClosed $event) use ($auction, $buyer2) {
            return $event->auction->id === $auction->id
                && $event->auction->winner_id === $buyer2->id;
        });
    }

    public function test_alt_path_no_bids_ends_with_no_winner(): void
    {
        Event::fake([AuctionClosed::class]);

        $auction = Auction::factory()->for($this->artisan)->create([
            'category_id' => $this->category->id,
            'starting_price' => 100.00,
            'status' => AuctionStatus::Active,
            'is_published' => true,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subMinutes(15), // Expired
        ]);

        $closed = $this->auctionService->closeExpired();

        $this->assertCount(1, $closed);

        $auction->refresh();
        $this->assertEquals(AuctionStatus::Ended, $auction->status);
        $this->assertNull($auction->winner_id);
        $this->assertNull($auction->winning_bid_id);

        Event::assertDispatched(AuctionClosed::class, function (AuctionClosed $event) use ($auction) {
            return $event->auction->id === $auction->id
                && $event->auction->winner_id === null;
        });
    }

    public function test_alt_path_reserve_not_met_ends_with_no_winner(): void
    {
        Event::fake([AuctionClosed::class]);

        $auction = Auction::factory()->for($this->artisan)->create([
            'category_id' => $this->category->id,
            'starting_price' => 100.00,
            'reserve_price' => 300.00, // Reserve is 300
            'status' => AuctionStatus::Active,
            'is_published' => true,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subMinutes(15), // Expired
        ]);

        $buyer = User::factory()->buyer()->create();
        Bid::factory()->for($auction)->for($buyer)->create(['amount' => 200.00]); // Highest is 200 < 300

        $closed = $this->auctionService->closeExpired();

        $this->assertCount(1, $closed);

        $auction->refresh();
        $this->assertEquals(AuctionStatus::Ended, $auction->status);
        $this->assertNull($auction->winner_id);
        $this->assertNull($auction->winning_bid_id);

        Event::assertDispatched(AuctionClosed::class, function (AuctionClosed $event) use ($auction) {
            return $event->auction->id === $auction->id
                && $event->auction->winner_id === null;
        });
    }

    public function test_idempotency_running_twice_has_no_additional_effect(): void
    {
        Event::fake([AuctionClosed::class]);

        $auction = Auction::factory()->for($this->artisan)->create([
            'category_id' => $this->category->id,
            'starting_price' => 100.00,
            'status' => AuctionStatus::Active,
            'is_published' => true,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subMinutes(15),
        ]);

        // First run: closes the auction
        $firstRun = $this->auctionService->closeExpired();
        $this->assertCount(1, $firstRun);

        // Second run: auction is already ended, returns empty collection, zero side-effects
        $secondRun = $this->auctionService->closeExpired();
        $this->assertCount(0, $secondRun);

        // Event was only dispatched once
        Event::assertDispatchedTimes(AuctionClosed::class, 1);
    }

    public function test_future_active_auctions_are_not_closed(): void
    {
        Auction::factory()->for($this->artisan)->create([
            'category_id' => $this->category->id,
            'status' => AuctionStatus::Active,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(2), // Still running
        ]);

        $closed = $this->auctionService->closeExpired();

        $this->assertCount(0, $closed);
    }
}
