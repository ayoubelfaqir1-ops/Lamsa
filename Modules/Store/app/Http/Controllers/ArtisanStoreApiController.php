<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Store\Http\Requests\StoreStoreRequest;
use Modules\Store\Http\Requests\UpdateStoreRequest;
use Modules\Store\Http\Resources\StoreResource;
use Modules\Store\Models\Store;
use Modules\Store\Services\StoreService;
use RuntimeException;

class ArtisanStoreApiController extends Controller
{
    public function __construct(
        private readonly StoreService $storeService,
    ) {}

    /**
     * Get the authenticated artisan's store profile.
     */
    public function show(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        if (! $artisan) {
            return response()->json([
                'message' => 'Artisan profile not found.',
            ], 403);
        }

        $store = $artisan->store;

        if (! $store) {
            return response()->json([
                'message' => 'No store has been created yet.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'data' => new StoreResource($store->load('artisan.user')),
        ]);
    }

    /**
     * Create a store for the authenticated artisan.
     */
    public function store(StoreStoreRequest $request): JsonResponse
    {
        $this->authorize('create', Store::class);

        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;

        if (! $artisan) {
            return response()->json([
                'message' => 'Artisan profile required.',
            ], 403);
        }

        try {
            $store = $this->storeService->createStore(
                $artisan,
                $request->validated(),
                $request->file('logo')
            );

            return response()->json([
                'message' => 'Store created successfully.',
                'data' => new StoreResource($store),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update the authenticated artisan's store.
     */
    public function update(UpdateStoreRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;
        $store = $artisan?->store;

        if (! $store) {
            return response()->json([
                'message' => 'Store not found. Please create your store first.',
            ], 404);
        }

        $this->authorize('update', $store);

        $updatedStore = $this->storeService->updateStore(
            $store,
            $request->validated(),
            $request->file('logo')
        );

        return response()->json([
            'message' => 'Store updated successfully.',
            'data' => new StoreResource($updatedStore),
        ]);
    }

    /**
     * Remove the current store logo.
     */
    public function destroyLogo(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $artisan = $user->artisan;
        $store = $artisan?->store;

        if (! $store) {
            return response()->json([
                'message' => 'Store not found.',
            ], 404);
        }

        $this->authorize('update', $store);

        $this->storeService->deleteLogo($store);

        return response()->json([
            'message' => 'Store logo removed successfully.',
        ]);
    }
}
