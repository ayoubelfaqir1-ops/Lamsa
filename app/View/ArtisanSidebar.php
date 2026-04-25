<?php

namespace App\View;

use App\Models\Store;

class ArtisanSidebar
{
    public static function items(string $active, ?Store $store = null): array
    {
        $storeLink = $store
            ? route('artisan.store.show', $store)
            : route('artisan.store.create');

        return [
            self::item('overview', 'Dashboard Overview', route('artisan.dashboard'), 'home', $active),
            self::item('store', 'My Store', $storeLink, 'shop', $active),
            self::item('products', 'Products', route('artisan.products'), 'cube', $active),
            self::item('auctions', 'Auctions', route('artisan.auctions'), 'bolt', $active),
            self::item('bids', 'Bids', route('artisan.bids'), 'chart', $active),
            self::item('orders', 'Orders', route('artisan.orders'), 'bag', $active),
        ];
    }

    private static function item(string $key, string $label, string $href, string $icon, string $active): array
    {
        return [
            'label' => $label,
            'href' => $href,
            'icon' => $icon,
            'active' => $key === $active,
        ];
    }
}
