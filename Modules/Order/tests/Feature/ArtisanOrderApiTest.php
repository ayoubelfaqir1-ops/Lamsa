<?php

namespace Modules\Order\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtisanOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private User $artisanUser;

    private Artisan $artisan;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'buyer']);
        Role::firstOrCreate(['name' => 'artisan']);

        $this->artisanUser = User::factory()->create();
        $this->artisanUser->assignRole('artisan');

        $this->artisan = Artisan::factory()->create([
            'user_id' => $this->artisanUser->id,
            'status' => 'active',
        ]);
    }

    public function test_non_artisan_cannot_access_artisan_orders(): void
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');
        Sanctum::actingAs($buyer);

        $response = $this->getJson('/api/v1/artisan/orders');

        $response->assertStatus(403);
    }

    public function test_artisan_can_list_their_received_orders(): void
    {
        Sanctum::actingAs($this->artisanUser);

        Order::factory()->count(2)->create(['artisan_id' => $this->artisan->id]);
        Order::factory()->count(3)->create(); // other artisans' orders

        $response = $this->getJson('/api/v1/artisan/orders');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_artisan_can_view_order_details(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $order = Order::factory()->create(['artisan_id' => $this->artisan->id]);

        $response = $this->getJson("/api/v1/artisan/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_artisan_cannot_view_another_artisans_order(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $otherArtisan = Artisan::factory()->create(['status' => 'active']);
        $order = Order::factory()->create(['artisan_id' => $otherArtisan->id]);

        $response = $this->getJson("/api/v1/artisan/orders/{$order->id}");

        $response->assertForbidden();
    }

    public function test_artisan_can_update_order_status(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $order = Order::factory()->create([
            'artisan_id' => $this->artisan->id,
            'status' => OrderStatus::Processing,
        ]);

        $response = $this->patchJson("/api/v1/artisan/orders/{$order->id}/status", [
            'status' => OrderStatus::Shipped->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Shipped->value);

        $this->assertEquals(OrderStatus::Shipped, $order->fresh()->status);
    }

    public function test_artisan_cannot_perform_invalid_status_transition(): void
    {
        Sanctum::actingAs($this->artisanUser);

        // Pending cannot jump directly to Delivered
        $order = Order::factory()->create([
            'artisan_id' => $this->artisan->id,
            'status' => OrderStatus::Pending,
        ]);

        $response = $this->patchJson("/api/v1/artisan/orders/{$order->id}/status", [
            'status' => OrderStatus::Delivered->value,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Cannot transition order from pending to delivered.']);
    }

    public function test_unauthenticated_user_cannot_access_artisan_orders(): void
    {
        $response = $this->getJson('/api/v1/artisan/orders');

        $response->assertUnauthorized();
    }

    public function test_artisan_can_update_status_via_direct_order_patch(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $order = Order::factory()->create([
            'artisan_id' => $this->artisan->id,
            'status' => OrderStatus::Pending,
        ]);

        $response = $this->patchJson("/api/v1/artisan/orders/{$order->id}", [
            'status' => OrderStatus::Processing->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Processing->value);

        $this->assertEquals(OrderStatus::Processing, $order->fresh()->status);
    }

    public function test_artisan_cannot_update_another_artisans_order(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $otherArtisan = Artisan::factory()->create(['status' => 'active']);
        $order = Order::factory()->create([
            'artisan_id' => $otherArtisan->id,
            'status' => OrderStatus::Pending,
        ]);

        $response = $this->patchJson("/api/v1/artisan/orders/{$order->id}", [
            'status' => OrderStatus::Processing->value,
        ]);

        $response->assertForbidden();
    }

    public function test_artisan_can_complete_full_fulfillment_lifecycle(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $order = Order::factory()->create([
            'artisan_id' => $this->artisan->id,
            'status' => OrderStatus::Pending,
        ]);

        // 1. Pending -> Processing
        $this->patchJson("/api/v1/artisan/orders/{$order->id}", ['status' => OrderStatus::Processing->value])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Processing->value);

        // 2. Processing -> Shipped
        $this->patchJson("/api/v1/artisan/orders/{$order->id}", ['status' => OrderStatus::Shipped->value])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Shipped->value);

        // 3. Shipped -> Delivered
        $this->patchJson("/api/v1/artisan/orders/{$order->id}", ['status' => OrderStatus::Delivered->value])
            ->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Delivered->value);

        $this->assertEquals(OrderStatus::Delivered, $order->fresh()->status);
    }

    public function test_invalid_status_value_is_rejected_by_validation(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $order = Order::factory()->create([
            'artisan_id' => $this->artisan->id,
            'status' => OrderStatus::Pending,
        ]);

        $response = $this->patchJson("/api/v1/artisan/orders/{$order->id}", [
            'status' => 'invalid_status_value',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
