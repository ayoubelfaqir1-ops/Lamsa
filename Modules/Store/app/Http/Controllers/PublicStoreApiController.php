<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Product\Enums\ProductStatus;
use Modules\Store\Http\Resources\StoreResource;
use Modules\Store\Models\Store;

class PublicStoreApiController extends Controller
{
    /**
     * Display a public active store by its unique slug.
     */
    public function show(string $slug): JsonResponse
    {
        $store = Store::query()
            ->active()
            ->where('slug', $slug)
            ->with(['artisan.user'])
            ->withCount([
                'products' => fn ($query) => $query->where('status', ProductStatus::Active),
            ])
            ->first();

        if (! $store) {
            return response()->json([
                'message' => 'Store not found or is currently inactive.',
            ], 404);
        }

        return response()->json([
            'data' => new StoreResource($store),
        ]);
    }
}

