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

class ProductReviewApiTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'buyer']);
        Role::firstOrCreate(['name' => 'artisan']);

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
            'is_active' => true,
        ]);

        $category = Category::factory()->create([
            'is_active' => true,
        ]);

        $this->product = Product::factory()->create([
            'store_id' => $store->id,
            'artisan_id' => $artisan->id,
            'category_id' => $category->id,
            'status' => ProductStatus::Active,
            'is_published' => true,
        ]);
    }

    public function test_unauthenticated_user_cannot_submit_review(): void
    {
        $response = $this->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Great product!',
        ]);

        $response->assertUnauthorized();
    }

    public function test_buyer_can_submit_review_with_rating_and_comment(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Exceptional Moroccan craftsmanship, truly authentic!',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Review submitted successfully.')
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.comment', 'Exceptional Moroccan craftsmanship, truly authentic!')
            ->assertJsonPath('data.user.id', $this->buyer->id);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 5,
            'comment' => 'Exceptional Moroccan craftsmanship, truly authentic!',
        ]);
    }

    public function test_buyer_can_submit_rating_without_optional_comment(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 4,
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.comment', null);

        $this->assertDatabaseHas('reviews', [
            'user_id' => $this->buyer->id,
            'product_id' => $this->product->id,
            'rating' => 4,
            'comment' => null,
        ]);
    }

    public function test_duplicate_review_by_same_user_is_rejected(): void
    {
        Sanctum::actingAs($this->buyer);

        // 1. First review succeeds
        $firstResponse = $this->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 5,
            'comment' => 'First review.',
        ]);
        $firstResponse->assertStatus(201);

        // 2. Second review on the same product is rejected
        $secondResponse = $this->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 4,
            'comment' => 'Second attempt should fail.',
        ]);

        $secondResponse->assertStatus(422)
            ->assertJson([
                'message' => 'You have already reviewed this product.',
            ]);

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_different_buyers_can_each_review_the_same_product(): void
    {
        $secondBuyer = User::factory()->create();
        $secondBuyer->assignRole('buyer');

        // First buyer reviews
        Sanctum::actingAs($this->buyer);
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", ['rating' => 5])
            ->assertStatus(201);

        // Second buyer reviews
        Sanctum::actingAs($secondBuyer);
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", ['rating' => 3])
            ->assertStatus(201);

        $this->assertDatabaseCount('reviews', 2);
    }

    public function test_rating_range_validation(): void
    {
        Sanctum::actingAs($this->buyer);

        // Below minimum (0)
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", ['rating' => 0])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        // Above maximum (6)
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", ['rating' => 6])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        // Missing rating
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", ['comment' => 'No rating provided'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        // Non-integer rating
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", ['rating' => 'not-a-number'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_review_submission_for_nonexistent_product_returns_404(): void
    {
        Sanctum::actingAs($this->buyer);

        $response = $this->postJson('/api/v1/products/999999/reviews', [
            'rating' => 5,
        ]);

        $response->assertNotFound();
    }

    public function test_anyone_can_list_reviews_for_product(): void
    {
        Sanctum::actingAs($this->buyer);
        $this->postJson("/api/v1/products/{$this->product->id}/reviews", [
            'rating' => 5,
            'comment' => 'Beautiful artisan design.',
        ]);

        // Unauthenticated guest request
        $response = $this->getJson("/api/v1/products/{$this->product->id}/reviews");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rating', 5)
            ->assertJsonPath('data.0.comment', 'Beautiful artisan design.');
    }
}
