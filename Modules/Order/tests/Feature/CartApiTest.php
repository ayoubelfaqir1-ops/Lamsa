<?php

namespace Modules\Order\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\User;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Product;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CartApiTest extends TestCase
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

    public function test_unauthenticated_user_cannot_access_cart(): void
    {
        $response = $this->getJson('/api/v1/cart');

        $response->assertUnauthorized();
    }

    public function test_buyer_can_get_empty_cart(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonPath('data.total_items', 0)
            ->assertJsonPath('data.total_price', 0);
    }

    public function test_buyer_can_add_item_to_cart(): void
    {
        Sanctum::actingAs($this->buyer);
        $product = Product::factory()->create([
            'is_published' => true,
            'status' => ProductStatus::Active,
            'stock' => 10,
            'price' => 150.00,
        ]);

        $response = $this->postJson("/api/v1/cart/items/{$product->id}", [
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.product_id', $product->id)
            ->assertJsonPath('data.quantity', 2)
            ->assertJsonPath('data.subtotal', 300);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);
    }

    public function test_buyer_cannot_add_more_than_available_stock(): void
    {
        Sanctum::actingAs($this->buyer);
        $product = Product::factory()->create([
            'is_published' => true,
            'status' => ProductStatus::Active,
            'stock' => 3,
        ]);

        $response = $this->postJson("/api/v1/cart/items/{$product->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(422)
            ->assertJsonFragment(['message' => 'Requested quantity exceeds available stock (3 available).']);
    }

    public function test_buyer_cannot_add_inactive_or_unpublished_product(): void
    {
        Sanctum::actingAs($this->buyer);
        $unpublished = Product::factory()->create([
            'is_published' => false,
            'status' => ProductStatus::Active,
        ]);

        $response = $this->postJson("/api/v1/cart/items/{$unpublished->id}", [
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    public function test_buyer_can_update_cart_item_quantity(): void
    {
        Sanctum::actingAs($this->buyer);
        $product = Product::factory()->create([
            'is_published' => true,
            'status' => ProductStatus::Active,
            'stock' => 10,
        ]);

        $this->postJson("/api/v1/cart/items/{$product->id}", ['quantity' => 1]);

        $response = $this->patchJson("/api/v1/cart/items/{$product->id}", [
            'quantity' => 4,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.quantity', 4);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 4,
        ]);
    }

    public function test_buyer_can_remove_item_from_cart(): void
    {
        Sanctum::actingAs($this->buyer);
        $product = Product::factory()->create([
            'is_published' => true,
            'status' => ProductStatus::Active,
            'stock' => 10,
        ]);

        $this->postJson("/api/v1/cart/items/{$product->id}", ['quantity' => 2]);

        $response = $this->deleteJson("/api/v1/cart/items/{$product->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $product->id,
        ]);
    }

    public function test_buyer_can_clear_cart(): void
    {
        Sanctum::actingAs($this->buyer);
        $product1 = Product::factory()->create(['is_published' => true, 'status' => ProductStatus::Active, 'stock' => 5]);
        $product2 = Product::factory()->create(['is_published' => true, 'status' => ProductStatus::Active, 'stock' => 5]);

        $this->postJson("/api/v1/cart/items/{$product1->id}", ['quantity' => 1]);
        $this->postJson("/api/v1/cart/items/{$product2->id}", ['quantity' => 1]);

        $response = $this->deleteJson('/api/v1/cart');

        $response->assertOk();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_cart_can_be_synced_on_login(): void
    {
        Sanctum::actingAs($this->buyer);
        $product1 = Product::factory()->create(['is_published' => true, 'status' => ProductStatus::Active, 'stock' => 10]);
        $product2 = Product::factory()->create(['is_published' => true, 'status' => ProductStatus::Active, 'stock' => 5]);

        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 3],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.total_items', 5);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product2->id,
            'quantity' => 3,
        ]);
    }

    public function test_cart_flags_unavailable_items_when_product_is_unpublished_or_out_of_stock(): void
    {
        Sanctum::actingAs($this->buyer);
        $product = Product::factory()->create([
            'is_published' => true,
            'status' => ProductStatus::Active,
            'stock' => 5,
            'price' => 100,
        ]);

        $this->postJson("/api/v1/cart/items/{$product->id}", ['quantity' => 2]);

        // Product becomes out of stock or unpublished
        $product->update(['is_published' => false]);

        $response = $this->getJson('/api/v1/cart');

        $response->assertOk()
            ->assertJsonPath('data.has_unavailable_items', true)
            ->assertJsonPath('data.total_price', 0) // price should not include unavailable items
            ->assertJsonPath('data.items.0.is_available', false);
    }

    public function test_guest_cart_sync_caps_quantity_when_user_already_has_items_exceeding_stock(): void
    {
        Sanctum::actingAs($this->buyer);
        $product = Product::factory()->create([
            'is_published' => true,
            'status' => ProductStatus::Active,
            'stock' => 5,
        ]);

        // User already has 3 items in cart
        $this->postJson("/api/v1/cart/items/{$product->id}", ['quantity' => 3]);

        // Guest cart sends 4 items (3 + 4 = 7, but stock is 5)
        $response = $this->postJson('/api/v1/cart/sync', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 4],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.total_items', 5);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);
    }
}
