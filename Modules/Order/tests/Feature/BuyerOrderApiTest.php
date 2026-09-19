<?php

namespace Modules\Order\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Product\Models\Product;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BuyerOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'buyer']);
        Role::firstOrCreate(['name' => 'artisan']);

        $this->buyer = User::factory()->create();
        $this->buyer->assignRole('buyer');
    }

    public function test_unauthenticated_user_cannot_access_orders(): void
    {
        $response = $this->getJson('/api/v1/buyer/orders');

        $response->assertUnauthorized();
    }

    public function test_buyer_can_list_their_orders(): void
    {
        Sanctum::actingAs($this->buyer);

        Order::factory()->count(3)->create(['user_id' => $this->buyer->id]);
        Order::factory()->count(2)->create(); // other users' orders

        $response = $this->getJson('/api/v1/buyer/orders');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'status', 'total_amount', 'shipping_address'],
                ],
                'meta' => ['current_page', 'total'],
            ]);
    }

    public function test_buyer_can_view_single_order(): void
    {
        Sanctum::actingAs($this->buyer);

        $order = Order::factory()->create(['user_id' => $this->buyer->id]);

        $response = $this->getJson("/api/v1/buyer/orders/{$order->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_buyer_cannot_view_another_buyers_order(): void
    {
        Sanctum::actingAs($this->buyer);

        $otherBuyer = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $otherBuyer->id]);

        $response = $this->getJson("/api/v1/buyer/orders/{$order->id}");

        $response->assertForbidden();
    }

    public function test_buyer_can_cancel_pending_order_and_stock_is_restored(): void
    {
        Sanctum::actingAs($this->buyer);

        $product = Product::factory()->create(['stock' => 5]);
        $artisan = Artisan::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $this->buyer->id,
            'artisan_id' => $artisan->id,
            'status' => OrderStatus::Pending,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artisan_id' => $artisan->id,
            'quantity' => 2,
        ]);

        $response = $this->postJson("/api/v1/buyer/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);

        $this->assertEquals(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertEquals(7, $product->fresh()->stock); // 5 + 2 restored
    }

    public function test_buyer_cannot_cancel_shipped_order(): void
    {
        Sanctum::actingAs($this->buyer);

        $order = Order::factory()->create([
            'user_id' => $this->buyer->id,
            'status' => OrderStatus::Shipped,
        ]);

        $response = $this->postJson("/api/v1/buyer/orders/{$order->id}/cancel");

        $response->assertForbidden();
    }

    public function test_cancelling_order_restores_stock_even_if_product_was_soft_deleted(): void
    {
        Sanctum::actingAs($this->buyer);

        $product = Product::factory()->create(['stock' => 5]);
        $artisan = Artisan::factory()->create();

        $order = Order::factory()->create([
            'user_id' => $this->buyer->id,
            'artisan_id' => $artisan->id,
            'status' => OrderStatus::Pending,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'artisan_id' => $artisan->id,
            'quantity' => 3,
        ]);

        // Artisan soft deletes the product
        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        // Buyer cancels pending order
        $response = $this->postJson("/api/v1/buyer/orders/{$order->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);

        // Product stock in database was restored (5 + 3 = 8) even while soft deleted
        $this->assertEquals(8, Product::withTrashed()->find($product->id)->stock);
    }
}
