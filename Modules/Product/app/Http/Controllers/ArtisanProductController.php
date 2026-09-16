<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Modules\Auth\Models\User;
use Modules\Product\Http\Requests\StoreProductRequest;
use Modules\Product\Http\Requests\UpdateProductRequest;
use Modules\Product\Models\Product;
use Modules\Product\Services\ArtisanProductService;

class ArtisanProductController extends Controller
{
    public function __construct(
        private readonly ArtisanProductService $productService,
    ) {}

    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $artisan = $user->artisan;
        $store = $artisan?->store;

        if (! $artisan || ! $store) {
            return response()->json([
                'message' => 'Create your store before managing products.',
            ], 403);
        }

        $data = $this->productService->getArtisanIndexData($artisan);

        return response()->json($data);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        /** @var User $user */
        $user = Auth::user();
        $artisan = $user->artisan;

        abort_if(! $artisan, 403, 'Artisan profile required.');

        $validated = $request->validated();

        $product = $this->productService->createProduct($artisan, $validated, $request);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product->load('category');

        return response()->json([
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $this->productService->updateProduct($product, $request->validated(), $request);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->fresh(),
        ]);
    }

    public function togglePublish(Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $isPublished = $this->productService->togglePublish($product);

        return response()->json([
            'message' => $isPublished ? 'Product published successfully.' : 'Product unpublished successfully.',
            'is_published' => $isPublished,
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return response()->json([
            'message' => 'Product deleted successfully.',
        ]);
    }
}
