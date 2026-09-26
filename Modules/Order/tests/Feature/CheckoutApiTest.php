<?php

namespace Modules\Order\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Product;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckoutApiTest extends TestCase
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

    public function test_unauthenticated_user_cannot_checkout(): void
    {
        $response = $this->postJson('/api/v1/checkout', [
            'shipping_address' => '123 Test Street, Casablanca',
        ]);

        $response->assertUnauthorized();
    }

    public function test_buyer_cannot_checkout_empty_cart(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson('/api/v1/checkout', [
            'shipping_address' => '123 Test Street, Casablanca',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Your cart is empty.']);
    }

    public function test_buyer_can_checkout_and_orders_are_split_by_artisan(): void
    {
        Sanctum::actingAs($this->buyer);

        $artisan1 = Artisan::factory()->create(['status' => 'active']);
        $artisan2 = Artisan::factory()->create(['status' => 'active']);

        $product1 = Product::factory()->create([
            'artisan_id' => $artisan1->id,
            'price' => 100.00,
            'stock' => 10,
            'is_published' => true,
            'status' => ProductStatus::Active,
        ]);

        $product2 = Product::factory()->create([
            'artisan_id' => $artisan2->id,
            'price' => 200.00,
            'stock' => 5,
            'is_published' => true,
            'status' => ProductStatus::Active,
        ]);

        // Add both to cart
        $this->postJson("/api/v1/cart/items/{$product1->id}", ['quantity' => 2]);
        $this->postJson("/api/v1/cart/items/{$product2->id}", ['quantity' => 1]);

        // Checkout
        $response = $this->postJson('/api/v1/checkout', [
            'shipping_address' => '123 Test Street, Casablanca',
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(201)
            ->assertJsonCount(2, 'data'); // 2 separate orders for 2 artisans

        // Verify orders in database
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'artisan_id' => $artisan1->id,
            'total_amount' => 200.00,
            'status' => OrderStatus::Pending->value,
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'artisan_id' => $artisan2->id,
            'total_amount' => 200.00,
            'status' => OrderStatus::Pending->value,
        ]);

        // Verify stock was decremented
        $this->assertEquals(8, $product1->fresh()->stock);
        $this->assertEquals(4, $product2->fresh()->stock);

        // Verify product name was snapshotted on order_items
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product1->id,
            'product_name' => $product1->name,
            'quantity' => 2,
            'unit_price' => 100.00,
        ]);

        // Verify buyer cart is now empty
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_fails_if_stock_became_insufficient(): void
    {
        Sanctum::actingAs($this->buyer);

        $product = Product::factory()->create([
            'stock' => 2,
            'is_published' => true,
            'status' => ProductStatus::Active,
        ]);

        $this->postJson("/api/v1/cart/items/{$product->id}", ['quantity' => 2]);

        // Stock changes before checkout
        $product->update(['stock' => 1]);

        $response = $this->postJson('/api/v1/checkout', [
            'shipping_address' => '123 Test Street, Casablanca',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => "Not enough stock for '{$product->name}' (1 remaining)."]);

        // Stock was not decremented
        $this->assertEquals(1, $product->fresh()->stock);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_uses_live_database_price_if_price_changed_after_adding_to_cart(): void
    {
        Sanctum::actingAs($this->buyer);
        $artisan = Artisan::factory()->create(['status' => 'active']);
        $product = Product::factory()->create([
            'artisan_id' => $artisan->id,
            'price' => 100.00,
            'stock' => 10,
            'is_published' => true,
            'status' => ProductStatus::Active,
        ]);

        $this->postJson("/api/v1/cart/items/{$product->id}", ['quantity' => 2]);

        // Artisan raises price to 175.00 before checkout
        $product->update(['price' => 175.00]);

        $response = $this->postJson('/api/v1/checkout', [
            'shipping_address' => '123 Test Street, Casablanca',
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.0.total_amount', 350); // 175 * 2 = 350

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'unit_price' => 175.00,
            'quantity' => 2,
        ]);
    }

    public function test_checkout_fails_if_product_in_cart_was_soft_deleted_before_checkout(): void
    {
        Sanctum::actingAs($this->buyer);
        $product = Product::factory()->create([
            'stock' => 5,
            'is_published' => true,
            'status' => ProductStatus::Active,
        ]);

        $this->postJson("/api/v1/cart/items/{$product->id}", ['quantity' => 1]);

        // Product is soft deleted
        $product->delete();

        $response = $this->postJson('/api/v1/checkout', [
            'shipping_address' => '123 Test Street, Casablanca',
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => "Product '{$product->name}' is no longer available."]);

        $this->assertDatabaseCount('orders', 0);
    }
}
