<?php

namespace Modules\Product\Observers;

use Illuminate\Support\Facades\Cache;
use Modules\Product\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "saved" event (covers created and updated).
     */
    public function saved(Product $product): void
    {
        $this->clearCache();
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
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

