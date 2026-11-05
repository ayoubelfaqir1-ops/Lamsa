<?php

namespace Modules\Product\Tests\Feature;

use App\Models\Favorite;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Models\Product;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FavoriteApiTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'buyer']);
        Role::firstOrCreate(['name' => 'artisan']);
        Role::firstOrCreate(['name' => 'admin']);

        $this->buyer = User::factory()->create();
        $this->buyer->assignRole('buyer');

        $artisanUser = User::factory()->create();
        $artisanUser->assignRole('artisan');

        $artisan = Artisan::factory()->create([
            'user_id' => $artisanUser->id,
            'status' => 'active',
        ]);

        $store = Store::factory()->create([
            'artisan_id' => $artisan->id,
            'name' => 'Test Store',
        ]);

        $this->product = Product::factory()->create([
            'artisan_id' => $artisan->id,
            'store_id' => $store->id,
            'name' => 'Handcrafted Leather Bag',
            'slug' => 'handcrafted-leather-bag',
            'is_published' => true,
        ]);
    }

    public function test_authenticated_user_can_toggle_favorite_on_and_off(): void
    {
        Sanctum::actingAs($this->buyer);

        // 1. Toggle ON
        $responseOn = $this->postJson("/api/v1/products/{$this->product->id}/favorite");

        $responseOn->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Product added to favorites',
                'data' => [
                    'favorited' => true,
                    'product_id' => $this->product->id,
                ],
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);

        // 2. Toggle OFF
        $responseOff = $this->postJson("/api/v1/products/{$this->product->id}/favorite");

        $responseOff->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Product removed from favorites',
                'data' => [
                    'favorited' => false,
                    'product_id' => $this->product->id,
                ],
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_authenticated_user_can_unfavorite_product_via_delete(): void
    {
        Sanctum::actingAs($this->buyer);

        Favorite::create([
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);

        $response = $this->deleteJson("/api/v1/products/{$this->product->id}/favorite");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Product removed from favorites',
                'data' => [
                    'favorited' => false,
                    'product_id' => $this->product->id,
                ],
            ]);

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_delete_favorite_is_idempotent_when_not_favorited(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->deleteJson("/api/v1/products/{$this->product->id}/favorite");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Product removed from favorites',
                'data' => [
                    'favorited' => false,
                    'product_id' => $this->product->id,
                ],
            ]);
    }

    public function test_user_can_toggle_favorite_by_slug(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson("/api/v1/products/{$this->product->slug}/favorite");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Product added to favorites',
                'data' => [
                    'favorited' => true,
                    'product_id' => $this->product->id,
                ],
            ]);

        $this->assertDatabaseHas('favorites', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
        ]);
    }

    public function test_guest_cannot_favorite_or_unfavorite_product(): void
    {
        $postResponse = $this->postJson("/api/v1/products/{$this->product->id}/favorite");
        $postResponse->assertUnauthorized();

        $deleteResponse = $this->deleteJson("/api/v1/products/{$this->product->id}/favorite");
        $deleteResponse->assertUnauthorized();
    }

    public function test_favorite_non_existent_product_returns_404(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson('/api/v1/products/999999/favorite');
        $response->assertNotFound();
    }

    public function test_authenticated_user_can_list_their_favorited_products(): void
    {
        Sanctum::actingAs($this->buyer);

        $secondProduct = Product::factory()->create([
            'name' => 'Ceramic Vase',
            'is_published' => true,
        ]);

        $thirdProduct = Product::factory()->create([
            'name' => 'Other User Item',
            'is_published' => true,
        ]);

        // Buyer favorites first and second
        Favorite::create(['user_id' => $this->buyer->id, 'product_id' => $this->product->id]);
        Favorite::create(['user_id' => $this->buyer->id, 'product_id' => $secondProduct->id]);

        // Another user favorites third product
        $anotherUser = User::factory()->create();
        Favorite::create(['user_id' => $anotherUser->id, 'product_id' => $thirdProduct->id]);

        $response = $this->getJson('/api/v1/favorites');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_guest_cannot_list_favorites(): void
    {
        $response = $this->getJson('/api/v1/favorites');
        $response->assertUnauthorized();
    }
}

