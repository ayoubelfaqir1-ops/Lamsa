<?php

namespace Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Http\Requests\UpdateOrderStatusRequest;
use Modules\Order\Http\Resources\OrderResource;
use Modules\Order\Models\Order;
use Modules\Order\Services\OrderService;
use RuntimeException;

class ArtisanOrderApiController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {}

    /**
     * List paginated orders received by the authenticated artisan.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->isArtisan() || ! $user->artisan) {
            return response()->json([
                'message' => 'User does not have an artisan profile.',
            ], 403);
        }

        $perPage = min((int) $request->input('per_page', 10), 50);
        $orders = $this->orderService->getArtisanOrders($user, $perPage);

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
     * Show details of an order received by the artisan.
     */
    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['items.product', 'user']);

        return response()->json([
            'data' => new OrderResource($order),
        ]);
    }

    /**
     * Update order status (e.g., shipped, delivered).
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('updateStatus', $order);

        try {
            $newStatus = OrderStatus::from((string) $request->validated('status'));
            $updatedOrder = $this->orderService->updateStatus($order, $newStatus);

            return response()->json([
                'message' => "Order status updated to {$newStatus->value}.",
                'data' => new OrderResource($updatedOrder),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
