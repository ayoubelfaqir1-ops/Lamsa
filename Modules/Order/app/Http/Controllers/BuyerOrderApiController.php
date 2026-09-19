<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Auth\Models\User;
use Modules\Order\Http\Resources\OrderResource;
use Modules\Order\Models\Order;
use Modules\Order\Services\OrderService;
use RuntimeException;

class BuyerOrderApiController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * List paginated orders placed by the authenticated buyer.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $perPage = min((int) $request->input('per_page', 10), 50);

        $orders = $this->orderService->getBuyerOrders($user, $perPage);

        return response()->json([
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Show details of a specific buyer order.
     */
    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['items.product', 'artisan.user', 'artisan.store']);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Cancel a pending buyer order.
     */
    public function cancel(Order $order): JsonResponse
    {
        Gate::authorize('cancel', $order);

        try {
            $cancelledOrder = $this->orderService->cancelOrder($order);

            return response()->json([
                'message' => 'Order cancelled successfully.',
                'data' => new OrderResource($cancelledOrder),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
