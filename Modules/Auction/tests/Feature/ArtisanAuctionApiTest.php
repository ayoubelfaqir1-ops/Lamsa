<?php

namespace Modules\Auction\Tests\Feature;

use App\Enums\ArtisanStatus;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Models\Category;
use Tests\TestCase;

class ArtisanAuctionApiTest extends TestCase
{
    use RefreshDatabase;

    private User $artisanUser;

    private Artisan $artisan;

    private Category $category;

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

        $this->category = Category::factory()->create(['is_active' => true]);
    }

    public function test_non_artisan_cannot_create_auction(): void
    {
        $buyer = User::factory()->buyer()->create();

        $response = $this->actingAs($buyer)
            ->postJson('/api/v1/artisan/auctions', [
                'category_id' => $this->category->id,
                'name' => 'Handmade Rug',
                'starting_price' => 100,
                'starts_at' => now()->toIso8601String(),
                'ends_at' => now()->addDays(3)->toIso8601String(),
            ]);

        $response->assertStatus(403);
    }

    public function test_pending_artisan_cannot_create_auction(): void
    {
        $pendingUser = User::factory()->create();
        $artisan = Artisan::factory()->create([
            'user_id' => $pendingUser->id,
            'status' => ArtisanStatus::Pending,
        ]);
        Store::factory()->create(['artisan_id' => $artisan->id]);
        $pendingUser->assignRole('artisan');

        $response = $this->actingAs($pendingUser)
            ->postJson('/api/v1/artisan/auctions', [
                'category_id' => $this->category->id,
                'name' => 'Handmade Rug',
                'starting_price' => 100,
                'starts_at' => now()->toIso8601String(),
                'ends_at' => now()->addDays(3)->toIso8601String(),
            ]);

        $response->assertStatus(403);
    }

    public function test_approved_artisan_can_create_active_auction_when_starts_now(): void
    {
        $response = $this->actingAs($this->artisanUser)
            ->postJson('/api/v1/artisan/auctions', [
                'category_id' => $this->category->id,
                'name' => 'Ceramic Tagine',
                'description' => 'Authentic clay tagine',
                'starting_price' => 80.00,
                'reserve_price' => 120.00,
                'starts_at' => now()->subMinute()->toIso8601String(),
                'ends_at' => now()->addDays(5)->toIso8601String(),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Ceramic Tagine')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.starting_price', 80)
            ->assertJsonPath('data.current_price', 80)
            ->assertJsonPath('data.reserve_price', 120);

        $this->assertDatabaseHas('auctions', [
            'name' => 'Ceramic Tagine',
            'status' => 'active',
            'artisan_id' => $this->artisan->id,
        ]);
    }

    public function test_approved_artisan_can_create_scheduled_auction_when_starts_in_future(): void
    {
        $response = $this->actingAs($this->artisanUser)
            ->postJson('/api/v1/artisan/auctions', [
                'category_id' => $this->category->id,
                'name' => 'Leather Bag',
                'starting_price' => 150.00,
                'starts_at' => now()->addDays(2)->toIso8601String(),
                'ends_at' => now()->addDays(7)->toIso8601String(),
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'scheduled');

        $this->assertDatabaseHas('auctions', [
            'name' => 'Leather Bag',
            'status' => 'scheduled',
        ]);
    }

    public function test_rejects_if_ends_at_is_before_starts_at(): void
    {
        $response = $this->actingAs($this->artisanUser)
            ->postJson('/api/v1/artisan/auctions', [
                'category_id' => $this->category->id,
                'name' => 'Invalid Dates Auction',
                'starting_price' => 50.00,
                'starts_at' => now()->addDays(5)->toIso8601String(),
                'ends_at' => now()->addDays(2)->toIso8601String(),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ends_at']);
    }

    public function test_artisan_can_list_their_auctions_with_summary(): void
    {
        Auction::factory()->for($this->artisan)->create([
            'status' => AuctionStatus::Active,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(2),
        ]);

        Auction::factory()->for($this->artisan)->create([
            'status' => AuctionStatus::Scheduled,
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(5),
        ]);

        $response = $this->actingAs($this->artisanUser)
            ->getJson('/api/v1/artisan/auctions');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('summary.liveAuctions', 1)
            ->assertJsonPath('summary.scheduledAuctions', 1);
    }

    public function test_artisan_can_update_auction_before_it_starts_and_has_no_bids(): void
    {
        $auction = Auction::factory()->for($this->artisan)->create([
            'status' => AuctionStatus::Scheduled,
            'starts_at' => now()->addDays(1),
            'ends_at' => now()->addDays(5),
            'name' => 'Original Name',
        ]);

        $response = $this->actingAs($this->artisanUser)
            ->patchJson("/api/v1/artisan/auctions/{$auction->id}", [
                'name' => 'Updated Name',
                'starting_price' => 200,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name')
            ->assertJsonPath('data.starting_price', 200);

        $this->assertDatabaseHas('auctions', [
            'id' => $auction->id,
            'name' => 'Updated Name',
            'starting_price' => 200,
        ]);
    }

    public function test_artisan_cannot_update_auction_if_bids_already_exist(): void
    {
        $auction = Auction::factory()->for($this->artisan)->create([
            'status' => AuctionStatus::Active,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(2),
        ]);

        Bid::factory()->for($auction)->create(['amount' => 150]);

        $response = $this->actingAs($this->artisanUser)
            ->patchJson("/api/v1/artisan/auctions/{$auction->id}", [
                'name' => 'Illegal Update',
            ]);

        $response->assertStatus(403);
    }

    public function test_artisan_can_cancel_active_auction(): void
    {
        $auction = Auction::factory()->for($this->artisan)->create([
            'status' => AuctionStatus::Active,
        ]);

        $response = $this->actingAs($this->artisanUser)
            ->deleteJson("/api/v1/artisan/auctions/{$auction->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('auctions', [
            'id' => $auction->id,
            'status' => 'cancelled',
            'is_published' => false,
        ]);
    }
}
