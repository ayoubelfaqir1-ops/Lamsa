<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Auth\Models\User;
use Modules\Order\Http\Requests\CheckoutRequest;
use Modules\Order\Http\Resources\OrderResource;
use Modules\Order\Services\OrderCheckoutService;
use RuntimeException;

class CheckoutApiController extends Controller
{
    public function __construct(
        private readonly OrderCheckoutService $orderCheckoutService,
    ) {}

    /**
     * Process checkout for the authenticated user's cart.
     */
    public function __invoke(CheckoutRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        try {
            $createdOrders = $this->orderCheckoutService->checkout($user, $request->validated());

            return response()->json([
                'message' => 'Orders placed successfully.',
                'data' => OrderResource::collection($createdOrders),
            ], 201);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
