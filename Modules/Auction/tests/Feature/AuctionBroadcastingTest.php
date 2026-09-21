<?php

namespace Modules\Auction\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auction\Models\Auction;
use Modules\Auth\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuctionBroadcastingTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private Auction $auction;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'buyer', 'guard_name' => 'api']);
        Role::firstOrCreate(['name' => 'artisan', 'guard_name' => 'api']);

        $this->buyer = User::factory()->create();
        $this->buyer->assignRole('buyer');

        $this->auction = Auction::factory()->create();
    }

    public function test_authenticated_user_can_authorize_for_auction_private_channel(): void
    {
        $response = $this->actingAs($this->buyer, 'sanctum')
            ->postJson('/broadcasting/auth', [
                'channel_name' => 'private-auction.'.$this->auction->id,
                'socket_id' => '1234.5678',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['auth']);
    }

    public function test_unauthenticated_guest_cannot_authorize_for_auction_private_channel(): void
    {
        $response = $this->postJson('/broadcasting/auth', [
            'channel_name' => 'private-auction.'.$this->auction->id,
            'socket_id' => '1234.5678',
        ]);

        $response->assertStatus(403);
    }
}
