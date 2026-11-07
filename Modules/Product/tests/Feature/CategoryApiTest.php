<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'artisan']);
    }

    public function test_categories_returns_only_active(): void
    {
        Category::factory()->create(['name' => 'Active', 'is_active' => true]);
        Category::factory()->create(['name' => 'Inactive', 'is_active' => false]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Active');
    }

    public function test_categories_include_visible_product_count(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        // Visible
        Product::factory()->create(['category_id' => $category->id]);

        // Invisible
        Product::factory()->unpublished()->create(['category_id' => $category->id]);
        Product::factory()->inactive()->create(['category_id' => $category->id]);
        Product::factory()->suspended()->create(['category_id' => $category->id]);
        Product::factory()->pending()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.products_count', 1);
    }

    public function test_categories_ordered_by_name(): void
    {
        Category::factory()->create(['name' => 'Zebra', 'is_active' => true]);
        Category::factory()->create(['name' => 'Apple', 'is_active' => true]);
        Category::factory()->create(['name' => 'Mango', 'is_active' => true]);

        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Apple')
            ->assertJsonPath('data.1.name', 'Mango')
            ->assertJsonPath('data.2.name', 'Zebra');
    }

    public function test_categories_list_is_cached(): void
    {
        Cache::flush();
        $this->assertFalse(Cache::has('active_categories_list'));

        Category::factory()->create(['name' => 'Textiles', 'is_active' => true]);

        // First call caches the result
        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has('active_categories_list'));
    }

    public function test_category_cache_invalidated_when_category_updated(): void
    {
        Cache::flush();
        $category = Category::factory()->create(['name' => 'Ceramics', 'is_active' => true]);

        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has('active_categories_list'));

        $category->update(['name' => 'Moroccan Ceramics']);
        $this->assertFalse(Cache::has('active_categories_list'));
    }

    public function test_category_cache_invalidated_when_category_deleted(): void
    {
        Cache::flush();
        $category = Category::factory()->create(['name' => 'Jewelry', 'is_active' => true]);

        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has('active_categories_list'));

        $category->delete();
        $this->assertFalse(Cache::has('active_categories_list'));
    }

    public function test_category_cache_invalidated_when_product_saved_or_deleted(): void
    {
        Cache::flush();
        $category = Category::factory()->create(['is_active' => true]);

        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has('active_categories_list'));

        // Adding a product invalidates count cache
        $product = Product::factory()->create(['category_id' => $category->id]);
        $this->assertFalse(Cache::has('active_categories_list'));

        // Query again to repopulate cache
        $this->getJson('/api/v1/categories')->assertOk();
        $this->assertTrue(Cache::has('active_categories_list'));

        // Deleting the product invalidates cache
        $product->delete();
        $this->assertFalse(Cache::has('active_categories_list'));
    }

    public function test_category_response_has_cache_control_headers(): void
    {
        Category::factory()->create(['is_active' => true]);

        $response = $this->getJson('/api/v1/categories');
        $this->assertStringContainsString('max-age=3600', (string) $response->headers->get('Cache-Control'));
    }
}
