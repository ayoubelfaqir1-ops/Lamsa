<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Artisan;
use Illuminate\Support\Facades\Cache;

class OrderStatsService
{
    public function forArtisan(Artisan $artisan): array
    {
        return Cache::remember(
            "artisan.orders.stats.{$artisan->id}",
            now()->addMinutes(5),
            function () use ($artisan): array {
                $statsQuery = $artisan->orders();

                $totalDelivered = (clone $statsQuery)
                    ->where('status', OrderStatus::Delivered)
                    ->count();

                $totalShipped = (clone $statsQuery)
                    ->where('status', OrderStatus::Shipped)
                    ->count();

                $totalPending = (clone $statsQuery)
                    ->where('status', OrderStatus::Pending)
                    ->count();

                $totalProcessing = (clone $statsQuery)
                    ->where('status', OrderStatus::Processing)
                    ->count();

                return compact(
                    'totalDelivered',
                    'totalShipped',
                    'totalPending',
                    'totalProcessing',
                );
            }
        );
    }
}
