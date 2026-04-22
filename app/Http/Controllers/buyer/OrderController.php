<?php

namespace App\Http\Controllers\Buyer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use App\Services\OrderCheckoutService;
use App\Services\OrderPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderCheckoutService $orderCheckoutService,
        private readonly OrderPaymentService $orderPaymentService,
    ) {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $orders = Order::query()
            ->with(['items.product', 'items.product.store'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->ensureBuyerOwnsOrder($order);

        $order->load(['items.product', 'items.product.store']);

        return view('orders.show', compact('order'));
    }

    public function cardPayment(Request $request): View|RedirectResponse
    {
        $orders = $this->resolvePendingOrders($request->query('orders'));
        if ($orders instanceof RedirectResponse) return $orders;

        $grandTotal = (float) $orders->sum('total_amount');

        return view('orders.payment', compact('orders', 'grandTotal'));
    }

    public function processCardPayment(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'orders' => ['required', 'string'],
        ]);

        $orders = $this->resolvePendingOrders($validated['orders']);
        if ($orders instanceof RedirectResponse) return $orders;
        $this->orderPaymentService->confirmCardPayment($orders);

        return redirect()
            ->route('orders.index')
            ->with('success', 'Card payment was confirmed and your orders are now processing.');
    }

    public function create(): RedirectResponse|View
    {
        $cartItems = $this->cartService->getCheckoutItems();

        if ($cartItems->isEmpty()) {
            return redirect()
                ->route('products.index')
                ->with('error', 'Your cart is empty.');
        }

        $total = $cartItems->sum(fn (Product $item) => $item->price * $item->quantity);

        return view('orders.create', compact('cartItems', 'total'));
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        try {
            $createdOrders = $this->orderCheckoutService->createPendingOrders($validated, Auth::id());
            $this->cartService->clear();

            return $validated['payment_method'] === 'cash'
                ? $this->finalizeCashOnDeliveryOrders($createdOrders)
                : $this->redirectToCardPayment($createdOrders);
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage() ?: 'Failed to place order. Please try again.');
        }
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->ensureBuyerOwnsOrder($order);

        if ($order->status !== OrderStatus::Pending) {
            return back()->with('error', 'Cannot cancel this order.');
        }

        $order->update(['status' => OrderStatus::Cancelled]);

        return back()->with('success', 'Order cancelled successfully.');
    }

    private function ensureBuyerOwnsOrder(Order $order): void
    {
        abort_unless($order->user_id === Auth::id(), 403);
    }

    private function resolvePendingOrders(?string $orderIdsString): \Illuminate\Support\Collection|RedirectResponse
    {
        try {
            return $this->orderPaymentService->loadPendingBuyerOrders((string) $orderIdsString, Auth::id());
        } catch (\RuntimeException $exception) {
            return redirect()
                ->route('orders.index')
                ->with('error', $exception->getMessage());
        }
    }

    private function finalizeCashOnDeliveryOrders(\Illuminate\Support\Collection $orders): RedirectResponse
    {
        $this->orderPaymentService->confirmCashOnDelivery($orders);

        return redirect()
            ->route('orders.index')
            ->with('success', $orders->count() > 1
                ? 'Your orders were confirmed with cash on delivery and are now processing.'
                : 'Your order was confirmed with cash on delivery and is now processing.');
    }

    private function redirectToCardPayment(\Illuminate\Support\Collection $orders): RedirectResponse
    {
        return redirect()
            ->route('orders.payment.card', ['orders' => $orders->pluck('id')->implode(',')])
            ->with('success', $orders->count() > 1
                ? 'Your orders were created and are waiting for card payment.'
                : 'Your order was created and is waiting for card payment.');
    }
}
