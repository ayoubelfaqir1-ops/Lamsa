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

        <!-- Performance Analytics (The Middle Section) -->
        <div class="overflow-hidden border border-slate-800 bg-[#111827] p-5 sm:p-6 lg:p-10">
            <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between lg:mb-12">
                <div class="space-y-2">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Revenue Metrics</h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Platform performance and growth trajectory</p>
                </div>
                <div class="flex flex-wrap gap-4 sm:justify-end">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 bg-[#10B981]"></div>
                        <span class="text-[9px] font-black uppercase text-slate-400">Gross Revenue</span>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto pb-2">
                <div class="flex h-48 min-w-[40rem] items-end gap-1.5 px-2 sm:min-w-0">
                    @foreach(range(1, 24) as $i)
                        <div class="group relative flex-1 cursor-pointer rounded-t-sm bg-emerald-500/20 transition-all hover:bg-emerald-400/80" style="height: {{ rand(20, 95) }}%">
                            <div class="pointer-events-none absolute -top-10 left-1/2 z-50 -translate-x-1/2 scale-90 bg-white px-3 py-1.5 text-[9px] font-black text-[#0f172a] opacity-0 shadow-xl transition-all group-hover:scale-100 group-hover:opacity-100 after:absolute after:left-1/2 after:top-full after:-translate-x-1/2 after:border-4 after:border-transparent after:border-t-white after:content-['']">
                                EUR {{ rand(120, 850) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <div class="mt-6 border-t border-slate-800/50 pt-6 sm:mt-8 sm:pt-8">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 sm:gap-8 lg:flex lg:gap-16">
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Conversion Rate</p>
                        <p class="text-xl font-light text-white">3.4% <span class="ml-2 text-xs font-bold text-emerald-500">+ 0.4%</span></p>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-1.5">Avg. Order Value</p>
                        <p class="text-xl font-light text-white">EUR 84.20 <span class="ml-2 text-xs font-bold text-slate-500">= 0.0%</span></p>
                    </div>
                </div>
            </div>
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
