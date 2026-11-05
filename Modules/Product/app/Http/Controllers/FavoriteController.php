<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Product\Http\Resources\ProductDetailResource;
use Modules\Product\Models\Product;

class FavoriteController extends Controller
{
    /**
     * List all products favorited by the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $favorites = Product::whereHas('favorites', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->with(['category', 'store', 'artisan.user'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest('id')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => ProductDetailResource::collection($favorites),
            'meta' => [
                'current_page' => $favorites->currentPage(),
                'last_page' => $favorites->lastPage(),
                'per_page' => $favorites->perPage(),
                'total' => $favorites->total(),
            ],
        ]);
    }

    /**
     * Toggle or delete the favorite status of a product for the authenticated user.
     */
    public function toggle(Request $request, string $id): JsonResponse
    {
        $product = $this->resolveProduct($id);
        /** @var User $user */
        $user = $request->user();

        if ($request->isMethod('delete')) {
            return $this->destroy($request, $id);
        }

        $existing = Favorite::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Product removed from favorites',
                'data' => [
                    'favorited' => false,
                    'product_id' => $product->id,
                ],
            ]);
        }

        Favorite::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Product added to favorites',
            'data' => [
                'favorited' => true,
                'product_id' => $product->id,
            ],
        ]);
    }

    /**
     * Explicitly remove product from favorites (unfavorite).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $product = $this->resolveProduct($id);
        /** @var User $user */
        $user = $request->user();

        Favorite::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Product removed from favorites',
            'data' => [
                'favorited' => false,
                'product_id' => $product->id,
            ],
        ]);
    }

    private function resolveProduct(string $idOrSlug): Product
    {
        return is_numeric($idOrSlug)
            ? Product::where('id', (int) $idOrSlug)->firstOrFail()
            : Product::where('slug', $idOrSlug)->firstOrFail();
    }
}
