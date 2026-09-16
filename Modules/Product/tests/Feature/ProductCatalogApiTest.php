<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Modules\Product\Models\Review;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'artisan']);
    }

    public function test_catalog_returns_only_published_active_products(): void
    {
        Product::factory()->create(['name' => 'Visible']);
        Product::factory()->unpublished()->create(['name' => 'Unpub']);
        Product::factory()->inactive()->create(['name' => 'Inactive']);
        Product::factory()->suspended()->create(['name' => 'Susp']);
        Product::factory()->pending()->create(['name' => 'Pend']);

        $response = $this->getJson('/api/v1/products');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Visible');
    }

    public function test_soft_deleted_products_never_appear(): void
    {
        Product::factory()->deleted()->create();
        $this->getJson('/api/v1/products')->assertJsonCount(0, 'data');
    }

    public function test_search_filters_by_name(): void
    {
        Product::factory()->create(['name' => 'Magic Carpet']);
        Product::factory()->create(['name' => 'Normal Rug']);

        $this->getJson('/api/v1/products?q=Magic')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Magic Carpet');
    }

    public function test_search_filters_by_description(): void
    {
        Product::factory()->create(['name' => 'Rug', 'description' => 'Made of magic']);
        Product::factory()->create(['name' => 'Rug 2', 'description' => 'Made of wool']);

        $this->getJson('/api/v1/products?q=magic')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Rug');
    }

    public function test_category_filter_returns_matching_products(): void
    {
        $cat1 = Category::factory()->create(['slug' => 'cat-1']);
        $cat2 = Category::factory()->create(['slug' => 'cat-2']);

        Product::factory()->create(['category_id' => $cat1->id, 'name' => 'P1']);
        Product::factory()->create(['category_id' => $cat2->id, 'name' => 'P2']);

        $this->getJson('/api/v1/products?category=cat-1')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'P1');
    }

    public function test_price_range_filter(): void
    {
        Product::factory()->create(['price' => 10, 'name' => 'P10']);
        Product::factory()->create(['price' => 50, 'name' => 'P50']);
        Product::factory()->create(['price' => 100, 'name' => 'P100']);

        $this->getJson('/api/v1/products?min_price=20&max_price=80')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'P50');
    }

    public function test_combined_category_and_price_filter(): void
    {
        $cat = Category::factory()->create(['slug' => 'cat']);
        Product::factory()->create(['category_id' => $cat->id, 'price' => 10]); // Too cheap
        Product::factory()->create(['category_id' => $cat->id, 'price' => 50, 'name' => 'Match']);
        Product::factory()->create(['price' => 50]); // Wrong category

        $this->getJson('/api/v1/products?category=cat&min_price=20&max_price=80')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Match');
    }

    public function test_sorting_by_price_ascending(): void
    {
        Product::factory()->create(['price' => 100, 'name' => 'Expensive']);
        Product::factory()->create(['price' => 10, 'name' => 'Cheap']);

        $this->getJson('/api/v1/products?sort=price_low')
            ->assertJsonPath('data.0.name', 'Cheap')
            ->assertJsonPath('data.1.name', 'Expensive');
    }

    public function test_sorting_by_price_descending(): void
    {
        Product::factory()->create(['price' => 10, 'name' => 'Cheap']);
        Product::factory()->create(['price' => 100, 'name' => 'Expensive']);

        $this->getJson('/api/v1/products?sort=price_high')
            ->assertJsonPath('data.0.name', 'Expensive')
            ->assertJsonPath('data.1.name', 'Cheap');
    }

    public function test_sorting_by_newest(): void
    {
        Product::factory()->create(['name' => 'Old', 'created_at' => now()->subDay()]);
        Product::factory()->create(['name' => 'New', 'created_at' => now()]);

        $this->getJson('/api/v1/products?sort=newest')
            ->assertJsonPath('data.0.name', 'New')
            ->assertJsonPath('data.1.name', 'Old');
    }

    public function test_sorting_by_rating(): void
    {
        $p1 = Product::factory()->create(['name' => 'Best']);
        $p2 = Product::factory()->create(['name' => 'Mid']);
        $p3 = Product::factory()->create(['name' => 'Unrated']);

        Review::factory()->create(['product_id' => $p1->id, 'rating' => 5]);
        Review::factory()->create(['product_id' => $p2->id, 'rating' => 3]);

        $this->getJson('/api/v1/products?sort=rating')
            ->assertJsonPath('data.0.name', 'Best')
            ->assertJsonPath('data.1.name', 'Mid')
            ->assertJsonPath('data.2.name', 'Unrated');
    }

    public function test_pagination_returns_correct_structure(): void
    {
        Product::factory()->create();

        $this->getJson('/api/v1/products')
            ->assertJsonStructure([
                'data',
                'links',
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_per_page_parameter_is_respected(): void
    {
        Product::factory()->count(10)->create();

        $this->getJson('/api/v1/products?per_page=3')
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.per_page', 3);
    }

    public function test_per_page_capped_at_50(): void
    {
        $this->getJson('/api/v1/products?per_page=100')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
    }

    public function test_invalid_sort_defaults_to_newest_or_validation_error(): void
    {
        $this->getJson('/api/v1/products?sort=garbage')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('sort');
    }

    public function test_invalid_price_range_returns_validation_error(): void
    {
        $this->getJson('/api/v1/products?min_price=100&max_price=50')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('max_price');
    }
}
