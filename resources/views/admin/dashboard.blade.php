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

    <div class="space-y-12">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8">
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
        <div class="bg-[#111827] border border-slate-800 p-10">
            <div class="flex justify-between items-center mb-12">
                <div class="space-y-2">
                    <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Revenue Metrics</h3>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Platform performance and growth trajectory</p>
                </div>
                <div class="flex gap-6">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 bg-[#10B981]"></div>
                        <span class="text-[9px] font-black uppercase text-slate-400">Gross Revenue</span>
                    </div>
                </div>
            </div>

            <div class="h-48 flex items-end gap-1.5 px-2">
                @foreach(range(1, 24) as $i)
                    <div class="flex-1 bg-emerald-500/20 hover:bg-emerald-400/80 transition-all cursor-pointer group relative rounded-t-sm" style="height: {{ rand(20, 95) }}%">
                        <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-white text-[#0f172a] text-[9px] font-black px-3 py-1.5 opacity-0 group-hover:opacity-100 transition-all transform scale-90 group-hover:scale-100 shadow-xl z-50 pointer-events-none after:content-[''] after:absolute after:top-full after:left-1/2 after:-translate-x-1/2 after:border-4 after:border-transparent after:border-t-white">
                            EUR {{ rand(120, 850) }}
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="flex justify-between mt-8 pt-8 border-t border-slate-800/50">
                <div class="flex gap-16">
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Top Artisans Section -->
            <div class="bg-[#1e293b] border border-slate-700 p-10">
                <div class="flex justify-between items-center mb-10 pb-6 border-b border-slate-700">
                    <div class="space-y-2">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Top Artisans</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Highest revenue generators this month</p>
                    </div>
                </div>
                
                <div class="space-y-8 text-slate-200">
                    @forelse($topArtisans as $artisan)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 bg-slate-800/50 border border-slate-700 flex items-center justify-center text-xs font-black uppercase text-white shrink-0 group-hover:border-[#10B981] group-hover:bg-[#10B981]/10 transition-all duration-500">
                                    {{ substr($artisan->user->name, 0, 2) }}
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-black uppercase tracking-widest text-white">{{ $artisan->user->name }}</p>
                                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-tight">{{ $artisan->craft_type }}</p>
                                </div>
                            </div>
                            <div class="text-right">
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
            <div class="bg-[#1e293b] border border-slate-700 p-10">
                <div class="flex justify-between items-center mb-10 pb-6 border-b border-slate-700">
                    <div class="space-y-2">
                        <h3 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Trending Products</h3>
                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Most popular items by unit sales</p>
                    </div>
                </div>
                
                <div class="space-y-8 text-slate-200">
                    @forelse($topProducts as $product)
                        <div class="flex items-center justify-between group">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 bg-slate-800/50 border border-slate-700 flex items-center justify-center text-xs font-black uppercase text-white shrink-0 group-hover:border-[#10B981] transition-all duration-500 overflow-hidden relative">
                                    @if(isset($product->primary_image))
                                        <img src="{{ $product->primary_image }}" class="w-full h-full object-cover group-hover:opacity-80 transition-opacity">
                                    @else
                                        <svg class="w-6 h-6 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @endif
                                </div>
                                <div class="space-y-1">
                                    <p class="text-xs font-black uppercase tracking-widest text-white">{{ Str::limit($product->name, 25) }}</p>
                                    <p class="text-[10px] text-slate-500 uppercase font-bold tracking-tight">{{ $product->category->name ?? 'Handmade' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
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
