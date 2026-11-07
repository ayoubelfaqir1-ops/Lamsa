<?php

namespace Modules\Product\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\User;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminCategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $buyer;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'buyer']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->buyer = User::factory()->create();
        $this->buyer->assignRole('buyer');
    }

    public function test_admin_can_list_categories(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create(['name' => 'Traditional Rugs']);
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->getJson('/api/v1/admin/categories');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'image',
                        'is_active',
                        'products_count',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('data.0.name', 'Traditional Rugs')
            ->assertJsonPath('data.0.products_count', 1);
    }

    public function test_admin_can_create_category_with_auto_generated_slug(): void
    {
        Sanctum::actingAs($this->admin);

        $payload = [
            'name' => 'Moroccan Ceramics',
            'description' => 'Authentic hand-painted ceramic plates and bowls.',
            'image' => 'https://example.com/ceramics.jpg',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/admin/categories', $payload);

        $response->assertCreated()
            ->assertJson([
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => [
                    'name' => 'Moroccan Ceramics',
                    'slug' => 'moroccan-ceramics',
                    'is_active' => true,
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Moroccan Ceramics',
            'slug' => 'moroccan-ceramics',
        ]);
    }

    public function test_admin_can_create_category_with_custom_slug(): void
    {
        Sanctum::actingAs($this->admin);

        $payload = [
            'name' => 'Leather Bags',
            'slug' => 'custom-leather-bags',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/admin/categories', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'custom-leather-bags');

        $this->assertDatabaseHas('categories', [
            'slug' => 'custom-leather-bags',
        ]);
    }

    public function test_admin_can_show_category(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create([
            'name' => 'Copper Crafts',
            'slug' => 'copper-crafts',
        ]);

        $response = $this->getJson("/api/v1/admin/categories/{$category->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $category->id,
                    'name' => 'Copper Crafts',
                    'slug' => 'copper-crafts',
                ],
            ]);
    }

    public function test_admin_can_update_category(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create([
            'name' => 'Old Name',
            'slug' => 'old-name',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Updated Pottery',
            'is_active' => false,
        ];

        $response = $this->patchJson("/api/v1/admin/categories/{$category->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Category updated successfully',
                'data' => [
                    'id' => $category->id,
                    'name' => 'Updated Pottery',
                    'is_active' => false,
                ],
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Pottery',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        Sanctum::actingAs($this->admin);

        $category = Category::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Category deleted successfully',
            ]);

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_category_validation_requires_name(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/categories', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_non_admin_cannot_access_category_crud(): void
    {
        Sanctum::actingAs($this->buyer);

        $category = Category::factory()->create();

        $this->getJson('/api/v1/admin/categories')->assertForbidden();
        $this->postJson('/api/v1/admin/categories', ['name' => 'New'])->assertForbidden();
        $this->getJson("/api/v1/admin/categories/{$category->id}")->assertForbidden();
        $this->patchJson("/api/v1/admin/categories/{$category->id}", ['name' => 'Updated'])->assertForbidden();
        $this->deleteJson("/api/v1/admin/categories/{$category->id}")->assertForbidden();
    }

    public function test_guest_cannot_access_category_crud(): void
    {
        $this->getJson('/api/v1/admin/categories')->assertUnauthorized();
    }
}

