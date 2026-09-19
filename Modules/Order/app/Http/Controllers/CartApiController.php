<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Models\User;
use Modules\Order\Http\Requests\AddCartItemRequest;
use Modules\Order\Http\Requests\SyncCartRequest;
use Modules\Order\Http\Requests\UpdateCartItemRequest;
use Modules\Order\Http\Resources\CartItemResource;
use Modules\Order\Http\Resources\CartResource;
use Modules\Order\Services\CartService;
use Modules\Product\Models\Product;
use RuntimeException;

class CartApiController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
    ) {}

    /**
     * Get the authenticated user's cart contents.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $cart = $this->cartService->getCart($user);

        return response()->json([
            'data' => new CartResource($cart),
        ]);
    }

    /**
     * Add an item to the cart.
     */
    public function store(AddCartItemRequest $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $item = $this->cartService->addItem($user, $product, (int) $request->validated('quantity'));

            return response()->json([
                'message' => 'Product added to cart successfully.',
                'data' => new CartItemResource($item),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update the quantity of an item in the cart.
     */
    public function update(UpdateCartItemRequest $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $item = $this->cartService->updateItem($user, $product, (int) $request->validated('quantity'));

            return response()->json([
                'message' => 'Cart item updated successfully.',
                'data' => new CartItemResource($item),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, Product $product): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->cartService->removeItem($user, $product);

        return response()->json([
            'message' => 'Product removed from cart.',
        ]);
    }

    /**
     * Clear all items from the cart.
     */
    public function clear(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->cartService->clear($user);

        return response()->json([
            'message' => 'Cart cleared successfully.',
        ]);
    }

    /**
     * Sync client-side guest cart items into the user's cart on login.
     */
    public function sync(SyncCartRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var array<int, array{product_id: int, quantity: int}> $items */
        $items = $request->validated('items');

        $cart = $this->cartService->sync($user, $items);

        return response()->json([
            'message' => 'Guest cart merged successfully.',
            'data' => new CartResource($cart),
        ]);
    }
}
