<?php

namespace App\Http\Controllers\Artisan;

use App\Enums\ArtisanStatus;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderStatsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderStatsService $orderStatsService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $artisan = $user->artisan;

        // If the user has no artisan profile and is just a buyer, redirect them out
        if (!$artisan && !$user->hasRole('artisan')) {
            return redirect()->route('home');
        }

        // If no artisan record exists yet but they have the role, or status is pending, show pending
        if (!$artisan || $artisan->status === ArtisanStatus::Pending) {
            return view('artisan.pending', [
                'status' => ArtisanStatus::Pending
            ]);
        }

        // Handle other non-active statuses (Suspended, Rejected)
        if ($artisan->status !== ArtisanStatus::Active) {
            return view('artisan.pending', [
                'status' => $artisan->status
            ]);
        }

        $ordersQuery = $artisan->orders()->with(['user', 'items.product']);

        $stats = $this->orderStatsService->forArtisan($artisan);

        $orders = $ordersQuery
            ->latest()
            ->paginate(10);

        return view('artisan.orders.index', compact(
            'orders',
        ) + $stats);
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order): View|RedirectResponse
    {
        $artisan = Auth::user()->artisan;

        if (!$artisan || $order->artisan_id !== $artisan->id) {
            return redirect()->route('artisan.orders');
        }

        $order->load(['user', 'items.product', 'items.product.store']);

        return view('artisan.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $artisan = Auth::user()->artisan;

        if (! $artisan || $order->artisan_id !== $artisan->id) {
            return redirect()->route('artisan.orders');
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:shipped,delivered'],
        ]);

        $nextStatus = OrderStatus::from($validated['status']);

        if (! $this->canTransitionTo($order->status, $nextStatus)) {
            return back()->with('error', 'This order cannot move to that status yet.');
        }

        $order->update(['status' => $nextStatus]);

        return back()->with('success', match ($nextStatus) {
            OrderStatus::Shipped => 'Order marked as shipped.',
            OrderStatus::Delivered => 'Order marked as delivered.',
            default => 'Order updated successfully.',
        });
    }

    private function canTransitionTo(OrderStatus $currentStatus, OrderStatus $nextStatus): bool
    {
        return match ($currentStatus) {
            OrderStatus::Processing => $nextStatus === OrderStatus::Shipped,
            OrderStatus::Shipped => $nextStatus === OrderStatus::Delivered,
            default => false,
        };
    }
}
