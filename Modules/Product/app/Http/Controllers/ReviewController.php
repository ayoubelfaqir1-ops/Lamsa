<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Product\Http\Requests\ReviewRequest;
use Modules\Product\Http\Resources\ReviewResource;
use Modules\Product\Models\Product;
use Modules\Product\Models\Review;

class ReviewController extends Controller
{
    /**
     * List all reviews for a product.
     */
    public function index(Request $request, string $idOrSlug): JsonResponse
    {
        $product = $this->resolveProduct($idOrSlug);
        $perPage = min((int) $request->input('per_page', 10), 50);

        $reviews = $product->reviews()
            ->with('user')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'data' => ReviewResource::collection($reviews->items()),
            'meta' => [
                'current_page' => $reviews->currentPage(),
                'last_page' => $reviews->lastPage(),
                'per_page' => $reviews->perPage(),
                'total' => $reviews->total(),
            ],
        ]);
    }

    /**
     * Submit a rating and review for a product.
     * Enforces the business rule: One review per (user_id, product_id).
     */
    public function store(ReviewRequest $request, string $idOrSlug): JsonResponse
    {
        $product = $this->resolveProduct($idOrSlug);

        /** @var User $user */
        $user = $request->user();

        // Enforce one review per (user_id, product_id)
        if ($product->reviews()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You have already reviewed this product.',
            ], 422);
        }

        /** @var Review $review */
        $review = $product->reviews()->create([
            'user_id' => $user->id,
            'rating' => (int) $request->validated('rating'),
            'comment' => $request->validated('comment'),
        ]);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'data' => new ReviewResource($review->load('user')),
        ], 201);
    }

    /**
     * Resolve product by ID or slug.
     */
    private function resolveProduct(string $idOrSlug): Product
    {
        return is_numeric($idOrSlug)
            ? Product::findOrFail((int) $idOrSlug)
            : Product::where('slug', $idOrSlug)->firstOrFail();
    }
}

