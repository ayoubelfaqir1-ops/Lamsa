<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Store\Models\Store;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminProductModerationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $artisanUser;

    private Artisan $artisan;

    private Store $store;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'artisan']);
        Role::firstOrCreate(['name' => 'buyer']);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->artisanUser = User::factory()->create();
        $this->artisanUser->assignRole('artisan');

        $this->artisan = Artisan::factory()->create([
            'user_id' => $this->artisanUser->id,
            'status' => 'active',
        ]);

        $this->store = Store::factory()->create([
            'artisan_id' => $this->artisan->id,
            'is_active' => true,
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Home Decor',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_product_moderation(): void
    {
        $response = $this->getJson('/api/v1/admin/products');

        $response->assertUnauthorized();
    }

    public function test_buyer_cannot_access_product_moderation(): void
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');
        Sanctum::actingAs($buyer);

        $response = $this->getJson('/api/v1/admin/products');

        $response->assertForbidden();
    }

    public function test_artisan_cannot_access_product_moderation(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $response = $this->getJson('/api/v1/admin/products');

        $response->assertForbidden();
    }

    public function test_admin_can_list_products_with_status_filter_and_pagination(): void
    {
        Sanctum::actingAs($this->adminUser);

        Product::factory()->count(3)->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'status' => ProductStatus::Pending,
        ]);

        Product::factory()->count(2)->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'status' => ProductStatus::Active,
        ]);

        $response = $this->getJson('/api/v1/admin/products?status=pending');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_view_single_product_details(): void
    {
        Sanctum::actingAs($this->adminUser);

        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'name' => 'Moderated Rug',
        ]);

        $response = $this->getJson("/api/v1/admin/products/{$product->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $product->id)
            ->assertJsonPath('data.name', 'Moderated Rug');
    }

    public function test_admin_can_update_product_status_and_publish_state(): void
    {
        Sanctum::actingAs($this->adminUser);

        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'status' => ProductStatus::Pending,
            'is_published' => false,
        ]);

        $response = $this->patchJson("/api/v1/admin/products/{$product->id}", [
            'status' => 'approved',
            'is_published' => true,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Product updated successfully.')
            ->assertJsonPath('data.status', ProductStatus::Active->value)
            ->assertJsonPath('data.is_published', true);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => ProductStatus::Active->value,
            'is_published' => true,
        ]);
    }

    public function test_admin_can_suspend_product(): void
    {
        Sanctum::actingAs($this->adminUser);

        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'status' => ProductStatus::Active,
        ]);

        $response = $this->patchJson("/api/v1/admin/products/{$product->id}", [
            'status' => ProductStatus::Suspended->value,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', ProductStatus::Suspended->value);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => ProductStatus::Suspended->value,
        ]);
    }

    public function test_admin_can_soft_delete_product(): void
    {
        Sanctum::actingAs($this->adminUser);

        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertOk()
            ->assertJson([
                'message' => 'Product soft deleted successfully.',
            ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_admin_can_restore_soft_deleted_product(): void
    {
        Sanctum::actingAs($this->adminUser);

        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
        ]);

        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        $response = $this->postJson("/api/v1/admin/products/{$product->id}/restore");

        $response->assertOk()
            ->assertJson([
                'message' => 'Product restored successfully.',
            ]);

        $this->assertNotSoftDeleted('products', [
            'id' => $product->id,
        ]);
    }

    public function test_invalid_status_choice_is_rejected(): void
    {
        Sanctum::actingAs($this->adminUser);

        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->patchJson("/api/v1/admin/products/{$product->id}", [
            'status' => 'nonexistent_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }
}
