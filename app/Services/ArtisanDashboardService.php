<?php

namespace App\Services;

use App\Enums\AuctionStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Models\Artisan;
use App\Models\Bid;
use App\Models\OrderItem;
use App\Models\Review;

class ArtisanDashboardService
{
    public function getDashboardData(Artisan $artisan): array
    {
        $monthlyRevenue = $this->monthlyRevenueQuery($artisan)->get();

        return [
            'totalReviews' => $this->totalReviews($artisan),
            'totalSales' => $this->totalSales($artisan),
            'totalActiveProducts' => $this->totalActiveProducts($artisan),
            'totalRevenue' => $this->totalRevenue($artisan),
            'revenueLabels' => $monthlyRevenue->pluck('month')->values(),
            'revenueValues' => $monthlyRevenue->pluck('revenue')->values(),
            'topProducts' => $this->topProducts($artisan),
            'recentOrders' => $this->recentOrders($artisan),
            'recentBids' => $this->recentBids($artisan),
        ];
    }

    private function totalReviews(Artisan $artisan): int
    {
        return Review::query()
            ->whereHas('product', fn ($query) => $query->where('artisan_id', $artisan->id))
            ->count();
    }

    private function totalSales(Artisan $artisan): int
    {
        return (int) OrderItem::query()
            ->where('artisan_id', $artisan->id)
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatus::Delivered))
            ->sum('quantity');
    }

    private function totalActiveProducts(Artisan $artisan): int
    {
        return $artisan->products()
            ->where('status', ProductStatus::Active)
            ->count();
    }

    private function totalRevenue(Artisan $artisan): float
    {
        return (float) (OrderItem::query()
            ->where('artisan_id', $artisan->id)
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatus::Delivered))
            ->selectRaw('SUM(unit_price * quantity) as total')
            ->value('total') ?? 0);
    }

    private function monthlyRevenueQuery(Artisan $artisan)
    {
        return OrderItem::query()
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month")
            ->selectRaw("SUM(quantity * unit_price) as revenue")
            ->where('artisan_id', $artisan->id)
            ->whereHas('order', fn ($query) => $query->where('status', OrderStatus::Delivered))
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month');
    }

    private function topProducts(Artisan $artisan)
    {
        return $artisan->products()
            ->withSum([
                'orderItems as total_units_sold' => function ($query) {
                    $query->whereHas('order', fn ($orderQuery) => $orderQuery->where('status', OrderStatus::Delivered));
                },
            ], 'quantity')
            ->orderByDesc('total_units_sold')
            ->take(5)
            ->get();
    }

    private function recentOrders(Artisan $artisan)
    {
        return OrderItem::query()
            ->with(['order.user', 'product'])
            ->where('artisan_id', $artisan->id)
            ->latest()
            ->take(5)
            ->get();
    }

    private function recentBids(Artisan $artisan)
    {
        return Bid::query()
            ->with(['auction', 'user'])
            ->whereHas('auction', function ($query) use ($artisan) {
                $query->where('artisan_id', $artisan->id)
                    ->where('status', AuctionStatus::Active);
            })
            ->latest()
            ->take(5)
            ->get();
    }
}
