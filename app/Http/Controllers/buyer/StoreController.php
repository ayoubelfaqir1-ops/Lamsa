<?php

namespace App\Http\Controllers\Buyer;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Services\StoreDetailService;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function __construct(
        private readonly StoreDetailService $storeDetailService,
    ) {
    }

    public function index(): View
    {
        $stores = Store::query()
            ->where('is_active', true)
            ->withCount([
                'products' => function ($query) {
                    $query->where('is_published', true)
                        ->where('status', ProductStatus::Active);
                },
            ])
            ->paginate(12);

        return view('stores.index', compact('stores'));
    }

    public function show(Store $store): View
    {
        return view('stores.show', $this->storeDetailService->build($store));
    }
}
