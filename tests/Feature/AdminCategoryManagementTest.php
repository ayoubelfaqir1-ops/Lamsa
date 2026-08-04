<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_empty_category(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category));

        $response
            ->assertRedirect(route('admin.categories'))
            ->assertSessionHas('success', 'Category deleted successfully.');

        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    public function test_admin_cannot_delete_category_that_still_has_products(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        $category = $product->category;

        $response = $this
            ->actingAs($admin)
            ->delete(route('admin.categories.destroy', $category));

        $response
            ->assertRedirect()
            ->assertSessionHasErrors([
                'delete' => 'This category still has products linked to it and cannot be deleted.',
            ]);

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
        ]);
    }
}
