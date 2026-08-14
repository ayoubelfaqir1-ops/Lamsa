@php
    $sidebarItems = [
        ['label' => 'My Feed', 'href' => route('dashboard'), 'icon' => 'home', 'active' => true],
        ['label' => 'My Favorites', 'href' => route('dashboard'), 'icon' => 'heart'],
        ['label' => 'Order History', 'href' => route('dashboard'), 'icon' => 'bag'],
        ['label' => 'Settings', 'href' => route('profile'), 'icon' => 'user'],
    ];
@endphp

<x-dashboard.layout header-title="User Dashboard">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-12">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <x-dashboard.stat-card 
                label="Orders Placed" 
                value="0" />
            
            <x-dashboard.stat-card 
                label="Favorite Items" 
                value="0" />

            <x-dashboard.stat-card 
                label="Active Auctions" 
                value="0" />
        </div>

        <!-- Recent Activity Placeholder -->
        <div class="bg-white border border-gray-100 p-10">
            <div class="flex justify-between items-center mb-8 pb-8 border-b border-gray-50">
                <h3 class="text-xs font-black uppercase tracking-widest text-black">My Collections</h3>
                <a href="{{ route('home') }}" class="text-[10px] font-bold uppercase tracking-widest text-gray-400 hover:text-black transition-colors">Start Exploring</a>
            </div>
            
            <div class="py-20 text-center space-y-4">
                <div class="text-gray-200 text-6xl font-light tracking-tighter uppercase opacity-20">
                    Lamsa
                </div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Discover handpicked artisanal heritage for your home.</p>
            </div>
        </div>
    </div>
</x-dashboard.layout>
