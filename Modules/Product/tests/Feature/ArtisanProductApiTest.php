<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Store\Models\Store;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArtisanProductApiTest extends TestCase
{
    use RefreshDatabase;

    private User $artisanUser;

    private Artisan $artisan;

    private Store $store;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        Role::firstOrCreate(['name' => 'buyer']);
        Role::firstOrCreate(['name' => 'artisan']);
        Role::firstOrCreate(['name' => 'admin']);

        $this->artisanUser = User::factory()->create();
        $this->artisanUser->assignRole('artisan');

        $this->artisan = Artisan::factory()->create([
            'user_id' => $this->artisanUser->id,
            'status' => 'active',
        ]);

        $this->store = Store::factory()->create([
            'artisan_id' => $this->artisan->id,
            'name' => 'Fes Pottery',
            'is_active' => true,
        ]);

        $this->category = Category::factory()->create([
            'name' => 'Ceramics',
            'is_active' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_access_artisan_products(): void
    {
        $response = $this->getJson('/api/v1/artisan/products');

        $response->assertStatus(401);
    }

    public function test_buyer_cannot_access_artisan_products(): void
    {
        $buyer = User::factory()->create();
        $buyer->assignRole('buyer');
        Sanctum::actingAs($buyer);

        $response = $this->getJson('/api/v1/artisan/products');

        $response->assertStatus(403);
    }

    public function test_artisan_without_store_cannot_manage_products(): void
    {
        $newArtisanUser = User::factory()->create();
        $newArtisanUser->assignRole('artisan');
        Artisan::factory()->create([
            'user_id' => $newArtisanUser->id,
            'status' => 'active',
        ]);

        Sanctum::actingAs($newArtisanUser);

        $response = $this->getJson('/api/v1/artisan/products');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Create your store before managing products.',
            ]);
    }

    public function test_artisan_can_list_their_products_with_metrics(): void
    {
        Product::factory()->count(3)->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'status' => ProductStatus::Active,
            'is_published' => true,
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->getJson('/api/v1/artisan/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'products',
                'totalProducts',
                'totalActive',
                'publishedProducts',
            ])
            ->assertJsonPath('totalProducts', 3)
            ->assertJsonPath('publishedProducts', 3);
    }

    public function test_artisan_can_create_product_with_images(): void
    {
        Sanctum::actingAs($this->artisanUser);

        $image1 = UploadedFile::fake()->image('bowl1.jpg', 600, 600);
        $image2 = UploadedFile::fake()->image('bowl2.jpg', 600, 600);

        $response = $this->postJson('/api/v1/artisan/products', [
            'category_id' => $this->category->id,
            'name' => 'Handmade Blue Ceramic Bowl',
            'description' => 'Traditional Moroccan ceramic bowl crafted in Fes.',
            'price' => 150.00,
            'stock' => 10,
            'images' => [$image1, $image2],
            'is_published' => true,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Product created successfully.')
            ->assertJsonPath('product.name', 'Handmade Blue Ceramic Bowl')
            ->assertJsonPath('product.slug', 'handmade-blue-ceramic-bowl')
            ->assertJsonPath('product.status', ProductStatus::Pending->value);

        $this->assertDatabaseHas('products', [
            'artisan_id' => $this->artisan->id,
            'store_id' => $this->store->id,
            'name' => 'Handmade Blue Ceramic Bowl',
            'slug' => 'handmade-blue-ceramic-bowl',
            'status' => ProductStatus::Pending->value,
            'price' => 150.00,
            'stock' => 10,
        ]);

        $product = Product::where('slug', 'handmade-blue-ceramic-bowl')->first();
        $this->assertNotNull($product);
        $this->assertCount(2, $product->images);

        foreach ($product->images as $imagePath) {
            Storage::disk('public')->assertExists($imagePath);
        }
    }

    public function test_artisan_can_view_their_product(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'name' => 'Handmade Tagine',
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->getJson("/api/v1/artisan/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('product.id', $product->id)
            ->assertJsonPath('product.name', 'Handmade Tagine');
    }

    public function test_artisan_can_update_product(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'name' => 'Original Tagine',
            'price' => 100.00,
            'stock' => 5,
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->putJson("/api/v1/artisan/products/{$product->id}", [
            'name' => 'Updated Royal Tagine',
            'price' => 125.50,
            'stock' => 8,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Product updated successfully.')
            ->assertJsonPath('product.name', 'Updated Royal Tagine')
            ->assertJsonPath('product.price', '125.50');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Royal Tagine',
            'price' => 125.50,
            'stock' => 8,
        ]);
    }

    public function test_artisan_can_toggle_publish_status(): void
    {
        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'is_published' => false,
        ]);

        Sanctum::actingAs($this->artisanUser);

        // Toggle: False -> True
        $response = $this->patchJson("/api/v1/artisan/products/{$product->id}/toggle-publish");

        $response->assertStatus(200)
            ->assertJsonPath('is_published', true);

        $this->assertTrue($product->fresh()->is_published);

        // Toggle: True -> False
        $response = $this->patchJson("/api/v1/artisan/products/{$product->id}/toggle-publish");

        $response->assertStatus(200)
            ->assertJsonPath('is_published', false);

        $this->assertFalse($product->fresh()->is_published);
    }

    public function test_artisan_can_delete_their_product(): void
    {
        $imagePath = 'products/images/sample.jpg';
        Storage::disk('public')->put($imagePath, 'dummy-content');

        $product = Product::factory()->create([
            'store_id' => $this->store->id,
            'artisan_id' => $this->artisan->id,
            'category_id' => $this->category->id,
            'images' => [$imagePath],
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->deleteJson("/api/v1/artisan/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product deleted successfully.',
            ]);

        $this->assertSoftDeleted('products', [
            'id' => $product->id,
        ]);

        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_artisan_cannot_view_another_artisans_product(): void
    {
        $otherArtisanUser = User::factory()->create();
        $otherArtisanUser->assignRole('artisan');
        $otherArtisan = Artisan::factory()->create(['user_id' => $otherArtisanUser->id]);
        $otherStore = Store::factory()->create(['artisan_id' => $otherArtisan->id]);

        $otherProduct = Product::factory()->create([
            'store_id' => $otherStore->id,
            'artisan_id' => $otherArtisan->id,
            'category_id' => $this->category->id,
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->getJson("/api/v1/artisan/products/{$otherProduct->id}");

        $response->assertStatus(403);
    }

    public function test_artisan_cannot_update_another_artisans_product(): void
    {
        $otherArtisanUser = User::factory()->create();
        $otherArtisanUser->assignRole('artisan');
        $otherArtisan = Artisan::factory()->create(['user_id' => $otherArtisanUser->id]);
        $otherStore = Store::factory()->create(['artisan_id' => $otherArtisan->id]);

        $otherProduct = Product::factory()->create([
            'store_id' => $otherStore->id,
            'artisan_id' => $otherArtisan->id,
            'category_id' => $this->category->id,
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->putJson("/api/v1/artisan/products/{$otherProduct->id}", [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
    }

    public function test_artisan_cannot_toggle_publish_on_another_artisans_product(): void
    {
        $otherArtisanUser = User::factory()->create();
        $otherArtisanUser->assignRole('artisan');
        $otherArtisan = Artisan::factory()->create(['user_id' => $otherArtisanUser->id]);
        $otherStore = Store::factory()->create(['artisan_id' => $otherArtisan->id]);

        $otherProduct = Product::factory()->create([
            'store_id' => $otherStore->id,
            'artisan_id' => $otherArtisan->id,
            'category_id' => $this->category->id,
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->patchJson("/api/v1/artisan/products/{$otherProduct->id}/toggle-publish");

        $response->assertStatus(403);
    }

    public function test_artisan_cannot_delete_another_artisans_product(): void
    {
        $otherArtisanUser = User::factory()->create();
        $otherArtisanUser->assignRole('artisan');
        $otherArtisan = Artisan::factory()->create(['user_id' => $otherArtisanUser->id]);
        $otherStore = Store::factory()->create(['artisan_id' => $otherArtisan->id]);

        $otherProduct = Product::factory()->create([
            'store_id' => $otherStore->id,
            'artisan_id' => $otherArtisan->id,
            'category_id' => $this->category->id,
        ]);

        Sanctum::actingAs($this->artisanUser);

        $response = $this->deleteJson("/api/v1/artisan/products/{$otherProduct->id}");

        $response->assertStatus(403);

        $this->assertNotSoftDeleted('products', [
            'id' => $otherProduct->id,
        ]);
    }
}
