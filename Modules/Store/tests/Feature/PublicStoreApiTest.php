<?php

namespace Modules\Store\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Models\Product;
use Modules\Store\Models\Store;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PublicStoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'artisan']);
    }

    public function test_public_user_can_view_active_store_by_slug(): void
    {
        $artisanUser = User::factory()->create(['name' => 'Fatima Zahra']);
        $artisanUser->assignRole('artisan');

        $artisan = Artisan::factory()->create([
            'user_id' => $artisanUser->id,
            'craft_type' => 'Leather',
            'city' => 'Fes',
            'status' => 'active',
        ]);

        $store = Store::factory()->create([
            'artisan_id' => $artisan->id,
            'name' => 'Fes Leather Crafts',
            'slug' => 'fes-leather-crafts',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'artisan_id' => $artisan->id,
            'status' => ProductStatus::Active,
        ]);

        $response = $this->getJson('/api/v1/stores/fes-leather-crafts');

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Fes Leather Crafts')
            ->assertJsonPath('data.artisan.name', 'Fatima Zahra')
            ->assertJsonPath('data.artisan.city', 'Fes')
            ->assertJsonPath('data.products_count', 1);
    }

    public function test_public_user_gets_404_for_inactive_store(): void
    {
        Store::factory()->inactive()->create([
            'slug' => 'inactive-store',
        ]);

        $response = $this->getJson('/api/v1/stores/inactive-store');

        $response->assertStatus(404);
    }

    public function test_public_user_gets_404_for_nonexistent_store(): void
    {
        $response = $this->getJson('/api/v1/stores/does-not-exist');

        $response->assertStatus(404);
    }
}

