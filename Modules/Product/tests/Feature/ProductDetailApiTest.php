<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Product;
use Modules\Product\Models\Review;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductDetailApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'artisan']);
    }

    public function test_published_product_detail_includes_relationships(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'slug',
                    'description',
                    'price',
                    'category' => ['id', 'name', 'slug'],
                    'store' => ['id', 'name', 'slug'],
                    'artisan' => ['id', 'name'],
                    'review_summary' => ['average_rating', 'count'],
                ],
            ]);
    }

    public function test_unpublished_product_returns_404(): void
    {
        $product = Product::factory()->unpublished()->create();
        $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
    }

    public function test_inactive_product_returns_404(): void
    {
        $product = Product::factory()->inactive()->create();
        $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
    }

    public function test_soft_deleted_product_returns_404(): void
    {
        $product = Product::factory()->deleted()->create();
        $this->getJson("/api/v1/products/{$product->slug}")->assertNotFound();
    }

    public function test_nonexistent_slug_returns_404(): void
    {
        $this->getJson('/api/v1/products/does-not-exist')->assertNotFound();
    }

    public function test_review_summary_is_accurate(): void
    {
        $product = Product::factory()->create();
        Review::factory()->create(['product_id' => $product->id, 'rating' => 4]);
        Review::factory()->create(['product_id' => $product->id, 'rating' => 5]);

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertOk()
            ->assertJsonPath('data.review_summary.average_rating', 4.5)
            ->assertJsonPath('data.review_summary.count', 2);
    }
}
