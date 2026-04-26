<?php

namespace App\Http\Controllers\admin;

use App\Enums\ArtisanStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Artisan;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with stats and pending requests.
     */
    public function index(): View
    {
        $stats = Cache::remember('admin.dashboard.stats', now()->addMinutes(5), function () {
            return [
                'totalArtisans' => Artisan::count(),
                'totalPendingRequests' => Artisan::where('status', ArtisanStatus::Pending)->count(),
                'totalActiveProducts' => Product::where('status', ProductStatus::Active)->count(),
                'platformRevenue' => Order::where('status', OrderStatus::Delivered)->sum('total_amount')
                    * config('lamsa.commission_rate', 0.10),
            ];
        });

        $totalArtisans = $stats['totalArtisans'];
        $totalPendingRequests = $stats['totalPendingRequests'];
        $totalActiveProducts = $stats['totalActiveProducts'];
        $platformRevenue = $stats['platformRevenue'];

        $topArtisans = Cache::remember('admin.dashboard.top-artisans', now()->addMinutes(60), function () {
            return Artisan::with('user')
                ->select('artisans.*')
                ->join('order_items', 'artisans.id', '=', 'order_items.artisan_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', OrderStatus::Delivered)
                ->selectRaw('SUM(order_items.quantity * order_items.unit_price) as total_revenue')
                ->groupBy('artisans.id')
                ->orderByDesc('total_revenue')
                ->take(5)
                ->get();
        });

        $topProducts = Cache::remember('admin.dashboard.top-products', now()->addMinutes(60), function () {
            return Product::with('category')
                ->select('products.*')
                ->join('order_items', 'products.id', '=', 'order_items.product_id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', OrderStatus::Delivered)
                ->selectRaw('SUM(order_items.quantity) as total_sales')
                ->groupBy('products.id')
                ->orderByDesc('total_sales')
                ->take(5)
                ->get();
        });

        return view('admin.dashboard', compact(
            'totalArtisans',
            'totalPendingRequests',
            'totalActiveProducts',
            'platformRevenue',
            'topArtisans',
            'topProducts'
        ));
    }
}
