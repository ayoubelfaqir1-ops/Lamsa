<?php

namespace Modules\CMS\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Modules\Auth\Models\User;
use Modules\CMS\Models\Page;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminPageCrudTest extends TestCase
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

    public function test_admin_can_list_pages(): void
    {
        Sanctum::actingAs($this->admin);

        Page::factory()->create(['title' => 'Terms of Service']);

        $response = $this->getJson('/api/v1/admin/pages');

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'content',
                        'is_published',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ])
            ->assertJsonPath('data.0.title', 'Terms of Service');
    }

    public function test_admin_can_create_page_with_auto_generated_slug(): void
    {
        Sanctum::actingAs($this->admin);

        $payload = [
            'title' => 'Privacy Policy',
            'content' => 'Here is our detailed privacy policy and cookie usage terms.',
            'is_published' => true,
        ];

        $response = $this->postJson('/api/v1/admin/pages', $payload);

        $response->assertCreated()
            ->assertJson([
                'status' => 'success',
                'message' => 'Page created successfully',
                'data' => [
                    'title' => 'Privacy Policy',
                    'slug' => 'privacy-policy',
                    'is_published' => true,
                ],
            ]);

        $this->assertDatabaseHas('pages', [
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
        ]);
    }

    public function test_admin_can_create_page_with_custom_slug(): void
    {
        Sanctum::actingAs($this->admin);

        $payload = [
            'title' => 'About Our Artisans',
            'slug' => 'about-us',
            'content' => 'Lamsa brings Moroccan artisans to the world.',
            'is_published' => true,
        ];

        $response = $this->postJson('/api/v1/admin/pages', $payload);

        $response->assertCreated()
            ->assertJsonPath('data.slug', 'about-us');

        $this->assertDatabaseHas('pages', [
            'slug' => 'about-us',
        ]);
    }

    public function test_admin_can_show_page(): void
    {
        Sanctum::actingAs($this->admin);

        /** @var Page $page */
        $page = Page::factory()->create([
            'title' => 'Frequently Asked Questions',
            'slug' => 'faq',
        ]);

        $response = $this->getJson("/api/v1/admin/pages/{$page->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'id' => $page->id,
                    'title' => 'Frequently Asked Questions',
                    'slug' => 'faq',
                ],
            ]);
    }

    public function test_admin_can_update_page(): void
    {
        Sanctum::actingAs($this->admin);

        /** @var Page $page */
        $page = Page::factory()->create([
            'title' => 'Old Title',
            'slug' => 'old-title',
            'is_published' => true,
        ]);

        $payload = [
            'title' => 'Updated Legal Terms',
            'is_published' => false,
        ];

        $response = $this->patchJson("/api/v1/admin/pages/{$page->id}", $payload);

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Page updated successfully',
                'data' => [
                    'id' => $page->id,
                    'title' => 'Updated Legal Terms',
                    'is_published' => false,
                ],
            ]);

        $this->assertDatabaseHas('pages', [
            'id' => $page->id,
            'title' => 'Updated Legal Terms',
            'is_published' => false,
        ]);
    }

    public function test_admin_can_delete_page(): void
    {
        Sanctum::actingAs($this->admin);

        /** @var Page $page */
        $page = Page::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/pages/{$page->id}");

        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'message' => 'Page deleted successfully',
            ]);

        $this->assertDatabaseMissing('pages', [
            'id' => $page->id,
        ]);
    }

    public function test_page_validation_requires_title_and_content(): void
    {
        Sanctum::actingAs($this->admin);

        $response = $this->postJson('/api/v1/admin/pages', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_non_admin_cannot_access_page_crud(): void
    {
        Sanctum::actingAs($this->buyer);

        /** @var Page $page */
        $page = Page::factory()->create();

        $this->getJson('/api/v1/admin/pages')->assertForbidden();
        $this->postJson('/api/v1/admin/pages', ['title' => 'New', 'content' => 'Text'])->assertForbidden();
        $this->getJson("/api/v1/admin/pages/{$page->id}")->assertForbidden();
        $this->patchJson("/api/v1/admin/pages/{$page->id}", ['title' => 'Updated'])->assertForbidden();
        $this->deleteJson("/api/v1/admin/pages/{$page->id}")->assertForbidden();
    }

    public function test_guest_cannot_access_page_crud(): void
    {
        $this->getJson('/api/v1/admin/pages')->assertUnauthorized();
    }
}
