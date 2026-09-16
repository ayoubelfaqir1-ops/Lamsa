<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
