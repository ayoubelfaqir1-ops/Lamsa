<?php

namespace Modules\Product\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\Category;

class CategoryObserver
{
    /**
     * Handle the Category "saved" event (covers created and updated).
     */
    public function saved(Category $category): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Category "deleted" event.
     */
    public function deleted(Category $category): void
    {
        $this->clearCache();
    }

    /**
     * Clear cached active categories list.
     */
    private function clearCache(): void
    {
        Cache::forget('active_categories_list');
    }
}

