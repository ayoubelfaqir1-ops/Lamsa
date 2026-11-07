<?php

namespace Modules\CMS\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_page_list_is_cached_and_invalidated_on_save(): void
    {
        Cache::flush();
        $this->assertFalse(Cache::has('cms_pages_list'));

        $page = Page::factory()->create(['title' => 'Terms', 'slug' => 'terms', 'is_published' => true]);

        // First call caches the result
        $this->getJson('/api/v1/pages')->assertOk();
        $this->assertTrue(Cache::has('cms_pages_list'));

        // Updating the page flushes the cache
        $page->update(['title' => 'Updated Terms']);
        $this->assertFalse(Cache::has('cms_pages_list'));
    }

    public function test_page_detail_is_cached_and_invalidated_on_update(): void
    {
        Cache::flush();
        $page = Page::factory()->create(['title' => 'FAQ', 'slug' => 'faq', 'is_published' => true]);

        $this->assertFalse(Cache::has('cms_page_faq'));

        // Calling show endpoint caches the detail
        $this->getJson('/api/v1/pages/faq')->assertOk();
        $this->assertTrue(Cache::has('cms_page_faq'));

        // Updating page content invalidates cache
        $page->update(['content' => 'Brand new FAQ answer']);
        $this->assertFalse(Cache::has('cms_page_faq'));
    }

    public function test_page_cache_invalidated_on_delete(): void
    {
        Cache::flush();
        $page = Page::factory()->create(['title' => 'Help', 'slug' => 'help', 'is_published' => true]);

        $this->getJson('/api/v1/pages')->assertOk();
        $this->getJson('/api/v1/pages/help')->assertOk();

        $this->assertTrue(Cache::has('cms_pages_list'));
        $this->assertTrue(Cache::has('cms_page_help'));

        $page->delete();

        $this->assertFalse(Cache::has('cms_pages_list'));
        $this->assertFalse(Cache::has('cms_page_help'));
    }

    public function test_public_responses_contain_cache_control_headers(): void
    {
        $page = Page::factory()->create(['title' => 'Legal', 'slug' => 'legal', 'is_published' => true]);

        $listResponse = $this->getJson('/api/v1/pages');
        $this->assertStringContainsString('max-age=3600', (string) $listResponse->headers->get('Cache-Control'));

        $detailResponse = $this->getJson('/api/v1/pages/legal');
        $this->assertStringContainsString('max-age=3600', (string) $detailResponse->headers->get('Cache-Control'));
    }
}
