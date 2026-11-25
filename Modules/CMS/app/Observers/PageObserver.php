<?php

namespace Modules\CMS\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\CMS\Models\Page;

class PageObserver
{
    /**
     * Handle the Page "saved" event (covers created and updated).
     */
    public function saved(Page $page): void
    {
        $this->clearCache($page);
    }

    /**
     * Handle the Page "deleted" event.
     */
    public function deleted(Page $page): void
    {
        $this->clearCache($page);
    }

    /**
     * Clear all related cache keys for the page.
     */
    private function clearCache(Page $page): void
    {
        // 1. Invalidate navigation list
        Cache::forget('cms_pages_list');

        // 2. Invalidate current slug detail
        Cache::forget("cms_page_{$page->slug}");

        // 3. If slug was updated, also invalidate old slug key
        if ($page->wasChanged('slug')) {
            $oldSlug = $page->getOriginal('slug');
            if (! empty($oldSlug)) {
                Cache::forget("cms_page_{$oldSlug}");
            }
        }
    }
}
