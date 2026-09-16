<?php

namespace Modules\Product\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Product\Models\Product;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCatalogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'artisan']);
    }

    public function test_visibility_scope_excludes_all_non_visible_statuses(): void
    {
        Product::factory()->create(); // active, published
        Product::factory()->unpublished()->create();
        Product::factory()->inactive()->create();
        Product::factory()->suspended()->create();
        Product::factory()->pending()->create();

        $this->assertEquals(1, Product::query()->visible()->count());
    }

    public function test_search_scope_matches_name_and_description(): void
    {
        Product::factory()->create(['name' => 'UniqueVase', 'description' => 'A nice item']);
        Product::factory()->create(['name' => 'Generic item', 'description' => 'Has UniqueVase inside']);
        Product::factory()->create(['name' => 'Something else', 'description' => 'Nothing']);

        $this->assertEquals(2, Product::query()->search('UniqueVase')->count());
    }

    public function test_price_range_scope_handles_null_bounds(): void
    {
        Product::factory()->create(['price' => 10]);
        Product::factory()->create(['price' => 20]);
        Product::factory()->create(['price' => 30]);

        $this->assertEquals(3, Product::query()->inPriceRange(null, null)->count());
        $this->assertEquals(2, Product::query()->inPriceRange(20, null)->count());
        $this->assertEquals(2, Product::query()->inPriceRange(null, 20)->count());
        $this->assertEquals(1, Product::query()->inPriceRange(15, 25)->count());
    }
}
