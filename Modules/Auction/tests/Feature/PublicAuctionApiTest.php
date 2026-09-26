<?php

namespace Modules\Auction\Tests\Feature;

use App\Enums\ArtisanStatus;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Models\Auction;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Models\Category;
use Tests\TestCase;

class PublicAuctionApiTest extends TestCase
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

        $this->category = Category::factory()->create(['slug' => 'pottery', 'is_active' => true]);
    }

    public function test_anyone_can_browse_public_auctions(): void
    {
        Auction::factory()->for($this->artisan)->create([
            'category_id' => $this->category->id,
            'status' => AuctionStatus::Active,
            'is_published' => true,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addDays(2),
        ]);

        $response = $this->getJson('/api/v1/auctions');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_can_filter_auctions_by_category(): void
    {
        $otherCategory = Category::factory()->create(['slug' => 'carpets', 'is_active' => true]);

        Auction::factory()->for($this->artisan)->create([
            'category_id' => $this->category->id,
            'name' => 'Pottery Vase',
        ]);

        Auction::factory()->for($this->artisan)->create([
            'category_id' => $otherCategory->id,
            'name' => 'Berber Rug',
        ]);

        $response = $this->getJson('/api/v1/auctions?category=pottery');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Pottery Vase');
    }

    public function test_public_detail_hides_reserve_price_from_buyers(): void
    {
        $auction = Auction::factory()->for($this->artisan)->create([
            'reserve_price' => 500.00,
            'starting_price' => 200.00,
            'status' => AuctionStatus::Active,
            'is_published' => true,
        ]);

        $buyer = User::factory()->buyer()->create();

        $response = $this->actingAs($buyer)
            ->getJson("/api/v1/auctions/{$auction->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.reserve_price', null);
    }

    public function test_owner_artisan_can_view_reserve_price_in_detail(): void
    {
        $auction = Auction::factory()->for($this->artisan)->create([
            'reserve_price' => 500.00,
            'starting_price' => 200.00,
            'status' => AuctionStatus::Active,
            'is_published' => true,
        ]);

        $response = $this->actingAs($this->artisanUser)
            ->getJson("/api/v1/auctions/{$auction->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('data.reserve_price', 500);
    }
}
