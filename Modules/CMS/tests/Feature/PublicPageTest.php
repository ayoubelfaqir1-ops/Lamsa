<?php

namespace Modules\CMS\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\CMS\Models\Page;
use Tests\TestCase;

class PublicPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_list_published_pages_only(): void
    {
        // 1. Arrange: Create published pages and an unpublished draft
        Page::factory()->create([
            'title' => 'Terms of Service',
            'slug' => 'terms-of-service',
            'is_published' => true,
        ]);

        Page::factory()->create([
            'title' => 'Privacy Policy',
            'slug' => 'privacy-policy',
            'is_published' => true,
        ]);

        Page::factory()->unpublished()->create([
            'title' => 'Draft Internal Policy',
            'slug' => 'internal-policy',
        ]);

        // 2. Act: Call public endpoint without authentication
        $response = $this->getJson('/api/v1/pages');

        // 3. Assert: Only 2 published pages are returned, and draft is excluded
        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'status' => 'success',
                'data' => [
                    [
                        'title' => 'Privacy Policy',
                        'slug' => 'privacy-policy',
                    ],
                    [
                        'title' => 'Terms of Service',
                        'slug' => 'terms-of-service',
                    ],
                ],
            ]);

        // Assert sensitive/internal fields are NOT exposed in list
        $response->assertJsonMissingPath('data.0.id')
            ->assertJsonMissingPath('data.0.is_published')
            ->assertJsonMissingPath('data.0.content');
    }

    public function test_guest_can_view_published_page_by_slug(): void
    {
        // 1. Arrange
        /** @var Page $page */
        $page = Page::factory()->create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'content' => '<h1>About Lamsa</h1><p>Crafted with love.</p>',
            'is_published' => true,
        ]);

        // 2. Act
        $response = $this->getJson('/api/v1/pages/about-us');

        // 3. Assert: Detail contains content and updated_at, but NO id or is_published
        $response->assertOk()
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'title' => 'About Us',
                    'slug' => 'about-us',
                    'content' => '<h1>About Lamsa</h1><p>Crafted with love.</p>',
                    'updated_at' => $page->updated_at?->toISOString(),
                ],
            ])
            ->assertJsonMissingPath('data.id')
            ->assertJsonMissingPath('data.is_published')
            ->assertJsonMissingPath('data.created_at');
    }

    public function test_guest_cannot_view_unpublished_page(): void
    {
        // 1. Arrange: Unpublished draft
        Page::factory()->unpublished()->create([
            'title' => 'Upcoming Holiday Promo Rules',
            'slug' => 'promo-rules',
        ]);

        // 2. Act
        $response = $this->getJson('/api/v1/pages/promo-rules');

        // 3. Assert: Must return 404 Not Found to prevent leaking draft existence
        $response->assertNotFound();
    }

    public function test_returns_404_when_page_does_not_exist(): void
    {
        $response = $this->getJson('/api/v1/pages/non-existent-page-slug');

        $response->assertNotFound();
    }
}

