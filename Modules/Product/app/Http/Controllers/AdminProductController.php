<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Product\Enums\ProductStatus;
use Modules\Product\Http\Requests\AdminUpdateProductRequest;
use Modules\Product\Models\Product;

class AdminProductController extends Controller
{
    /**
     * List products for admin moderation with status filters, search, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $query = Product::query()->with(['category', 'store', 'artisan.user']);

        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        } elseif ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

        if ($request->filled('status')) {
            $statusInput = (string) $request->input('status');
            $statusValue = $statusInput === 'approved' ? ProductStatus::Active->value : $statusInput;
            $query->where('status', $statusValue);
        }

        if ($request->has('is_published')) {
            $query->where('is_published', $request->boolean('is_published'));
        }

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    /**
     * Show details of a product for moderation review.
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::withTrashed()
            ->with(['category', 'store', 'artisan.user'])
            ->findOrFail($id);

        return response()->json([
            'data' => $product,
        ]);
    }

    /**
     * Moderate and update a product's status, publication flag, or attributes.
     */
    public function update(AdminUpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();

        if (isset($data['status'])) {
            $statusInput = (string) $data['status'];
            $data['status'] = $statusInput === 'approved'
                ? ProductStatus::Active
                : ProductStatus::from($statusInput);
        }

        $product->update($data);

        return response()->json([
            'message' => 'Product updated successfully.',
            'data' => $product->fresh(['category', 'store', 'artisan.user']),
        ]);
    }

    /**
     * Soft delete a product from moderation.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json([
            'message' => 'Product soft deleted successfully.',
        ]);
    }

    /**
     * Restore a previously soft-deleted product.
     */
    public function restore(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return response()->json([
            'message' => 'Product restored successfully.',
            'data' => $product->fresh(['category', 'store', 'artisan.user']),
        ]);
    }
}
