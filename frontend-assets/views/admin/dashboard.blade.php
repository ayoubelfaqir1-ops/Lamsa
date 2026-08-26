@php
    $sidebarItems = [
        ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => 'home', 'active' => true],
        ['label' => 'Artisans', 'href' => route('admin.artisans'), 'icon' => 'users'],
        ['label' => 'Categories', 'href' => route('admin.categories'), 'icon' => 'menu'],
    ];
@endphp

<x-dashboard.layout header-title="Administration Overview">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-8 lg:space-y-12">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-8">
            <x-dashboard.stat-card 
                label="Verified Artisans" 
                :value="$totalArtisans" />
            
            <x-dashboard.stat-card 
                label="Awaiting Approval" 
                :value="$totalPendingRequests" />

            <x-dashboard.stat-card 
                label="Studio Products" 
                :value="$totalActiveProducts" />

            <x-dashboard.stat-card 
                label="Lamsa Revenue" 
                :value="'EUR ' . number_format($platformRevenue, 2)" />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:gap-8">
            <!-- Top Artisans Section -->
            <div class="border border-slate-700 bg-[#1e293b] p-5 sm:p-6 lg:p-10">
                <div class="mb-8 flex justify-between border-b border-slate-700 pb-5 sm:items-center lg:mb-10 lg:pb-6">
                    <div class="space-y-2">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Top Artisans</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Highest revenue generators this month</p>
                    </div>
                </div>
                
                <div class="space-y-8 text-slate-200">
                    @forelse($topArtisans as $artisan)
                        <div class="group flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex items-center gap-4 sm:gap-5">
                                <div class="w-14 h-14 bg-slate-800/50 border border-slate-700 flex items-center justify-center text-xs font-black uppercase text-white shrink-0 group-hover:border-[#10B981] group-hover:bg-[#10B981]/10 transition-all duration-500">
                                    {{ substr($artisan->user->name, 0, 2) }}
                                </div>
                                <div class="min-w-0 space-y-1">
                                    <p class="truncate text-xs font-black uppercase tracking-widest text-white">{{ $artisan->user->name }}</p>
                                    <p class="truncate text-[10px] font-bold uppercase tracking-tight text-slate-500">{{ $artisan->craft_type }}</p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-black text-[#10B981]">EUR {{ number_format($artisan->total_revenue, 2) }}</p>
                                <p class="text-[9px] text-slate-600 font-bold uppercase tracking-tighter">Gross Revenue</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-20 text-center space-y-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-700">No revenue data found</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Top Products Section -->
            <div class="border border-slate-700 bg-[#1e293b] p-5 sm:p-6 lg:p-10">
                <div class="mb-8 flex justify-between border-b border-slate-700 pb-5 sm:items-center lg:mb-10 lg:pb-6">
                    <div class="space-y-2">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Trending Products</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Most popular items by unit sales</p>
                    </div>
                </div>
                
                <div class="space-y-8 text-slate-200">
                    @forelse($topProducts as $product)
                        <div class="group flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0 flex items-center gap-4 sm:gap-5">
                                <div class="w-14 h-14 bg-slate-800/50 border border-slate-700 flex items-center justify-center text-xs font-black uppercase text-white shrink-0 group-hover:border-[#10B981] transition-all duration-500 overflow-hidden relative">
                                    @if(isset($product->primary_image))
                                        <img src="{{ $product->primary_image }}" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                                    @else
                                        <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 space-y-1">
                                    <p class="truncate text-xs font-black uppercase tracking-widest text-white">{{ Str::limit($product->name, 25) }}</p>
                                    <p class="truncate text-[10px] font-bold uppercase tracking-tight text-slate-500">{{ $product->category->name ?? 'Handmade' }}</p>
                                </div>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-black text-white">{{ $product->total_sales }} sales</p>
                                <p class="text-[9px] text-slate-600 font-bold uppercase tracking-tighter">Units Sold</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-20 text-center space-y-3">
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-700">No sales data found</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-dashboard.layout>
