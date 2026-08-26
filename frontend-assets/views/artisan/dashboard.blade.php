@php
    $sidebarItems = \App\View\ArtisanSidebar::items('overview', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout header-title="Artisan Dashboard">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-6">
        <!-- Welcome Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">Dashboard Overview</h1>
                <p class="mt-1 text-sm text-slate-400">Welcome back! Here's what's happening with your store today.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('artisan.products.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#10B981] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#059669] focus:outline-none focus:ring-2 focus:ring-[#10B981]/50">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Product
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Stat Card 1 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Revenue</p>
                        <p class="text-2xl font-bold text-white">{{ number_format((float) $totalRevenue, 2) }} <span class="text-sm font-medium text-slate-500">MAD</span></p>
                    </div>
                </div>
            </div>

            <!-- Stat Card 2 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Sales</p>
                        <p class="text-2xl font-bold text-white">{{ $totalSales }}</p>
                    </div>
                </div>
            </div>

            <!-- Stat Card 3 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Active Listings</p>
                        <p class="text-2xl font-bold text-white">{{ $totalActiveProducts }}</p>
                    </div>
                </div>
            </div>

            <!-- Stat Card 4 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-500/10 text-purple-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Store Reviews</p>
                        <p class="text-2xl font-bold text-white">{{ $totalReviews }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Revenue Chart -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-6 shadow-sm backdrop-blur-sm lg:col-span-2">
                <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Revenue Overview</h2>
                        <p class="text-sm text-slate-400">Your earnings over the last 6 months</p>
                    </div>
                </div>
                <div class="relative h-72 w-full">
                    @if ($revenueValues->isNotEmpty())
                        <canvas id="artisanRevenueChart"></canvas>
                    @else
                        <div class="flex h-full items-center justify-center rounded-lg border border-dashed border-slate-700 bg-slate-800/30">
                            <p class="text-sm text-slate-500">No completed revenue data yet</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Top Products -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-6 shadow-sm backdrop-blur-sm">
                <div class="mb-5 border-b border-slate-800 pb-4">
                    <h2 class="text-lg font-semibold text-white">Top Performers</h2>
                    <p class="mt-1 text-sm text-slate-400">Products with highest completed sales</p>
                </div>
                <div class="space-y-5">
                    @forelse($topProducts ?? [] as $product)
                        @php
                            $productImage = collect($product->images ?? [])
                                ->filter()
                                ->map(fn ($image) => str_starts_with($image, 'http') ? $image : Storage::url($image))
                                ->first();
                        @endphp
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border border-slate-800 bg-slate-800/50">
                                @if($productImage)
                                    <img src="{{ $productImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-slate-500">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-white">{{ $product->name }}</p>
                                <div class="mt-1 flex items-center justify-between">
                                    <span class="text-xs text-slate-400">{{ $product->total_units_sold ?? 0 }} units sold</span>
                                    <span class="text-xs font-medium text-emerald-400">{{ number_format((float) $product->price, 2) }} MAD</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-sm text-slate-500">
                            <svg class="mx-auto mb-2 h-8 w-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            No products sold yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <!-- Recent Orders -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 shadow-sm backdrop-blur-sm">
                <div class="flex items-center justify-between border-b border-slate-800 p-6">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Recent Orders</h2>
                        <p class="mt-1 text-sm text-slate-400">Your latest sales activity</p>
                    </div>
                    <a href="{{ route('artisan.orders') }}" class="text-sm font-medium text-emerald-500 transition hover:text-emerald-400">View All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-900/50 text-slate-400">
                            <tr>
                                <th class="px-6 py-4 font-medium">Order ID</th>
                                <th class="px-6 py-4 font-medium">Product</th>
                                <th class="px-6 py-4 font-medium">Status</th>
                                <th class="px-6 py-4 text-right font-medium">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            @forelse ($recentOrders ?? [] as $orderItem)
                                <tr class="transition hover:bg-slate-800/30">
                                    <td class="px-6 py-4 font-medium text-white">#{{ $orderItem->order->order_number ?? $orderItem->order_id }}</td>
                                    <td class="px-6 py-4 text-slate-300">{{ $orderItem->product->name ?? 'Product' }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset
                                            @if($orderItem->order->status->value === 'processing') bg-blue-500/10 text-blue-400 ring-blue-500/20
                                            @elseif($orderItem->order->status->value === 'delivered') bg-emerald-500/10 text-emerald-400 ring-emerald-500/20
                                            @elseif($orderItem->order->status->value === 'shipped') bg-purple-500/10 text-purple-400 ring-purple-500/20
                                            @elseif($orderItem->order->status->value === 'cancelled') bg-rose-500/10 text-rose-400 ring-rose-500/20
                                            @else bg-slate-500/10 text-slate-400 ring-slate-500/20 @endif">
                                            {{ ucfirst($orderItem->order->status->value) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-white">{{ number_format($orderItem->unit_price * $orderItem->quantity, 2) }} MAD</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">
                                        No recent orders found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Auction Bids -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 shadow-sm backdrop-blur-sm">
                <div class="flex items-center justify-between border-b border-slate-800 p-6">
                    <div>
                        <h2 class="text-lg font-semibold text-white">Recent Auction Bids</h2>
                        <p class="mt-1 text-sm text-slate-400">Live bidding activity</p>
                    </div>
                    <a href="{{ route('artisan.bids') }}" class="text-sm font-medium text-emerald-500 transition hover:text-emerald-400">View All</a>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse ($recentBids as $bid)
                            <div class="flex items-center justify-between rounded-lg border border-slate-800 bg-slate-900/50 p-4 transition hover:border-slate-700">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500/10 text-amber-500">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $bid->auction?->name ?? 'Auction item' }}</p>
                                        <p class="mt-1 text-xs text-slate-400">Bid by <span class="font-medium text-slate-300">{{ $bid->user?->name ?? 'Unknown bidder' }}</span></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block rounded-md bg-amber-500/10 px-2.5 py-1 text-sm font-semibold text-amber-400">{{ number_format((float) $bid->amount, 2) }} MAD</span>
                                    <p class="mt-1.5 text-xs text-slate-500">{{ $bid->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-800 py-10">
                                <svg class="mb-3 h-10 w-10 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <p class="text-sm font-medium text-slate-400">No active bids</p>
                                <p class="mt-1 text-xs text-slate-500">When users bid on your auctions, they will appear here.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($revenueValues->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const artisanRevenueLabels = @json($revenueLabels);
                const artisanRevenueValues = @json($revenueValues);
                const artisanRevenueCanvas = document.getElementById('artisanRevenueChart');

                if (artisanRevenueCanvas) {
                    if (window.artisanRevenueChart instanceof Chart) {
                        window.artisanRevenueChart.destroy();
                    }

                    window.artisanRevenueChart = new Chart(artisanRevenueCanvas, {
                        type: 'line',
                        data: {
                            labels: artisanRevenueLabels,
                            datasets: [{
                                label: 'Revenue',
                                data: artisanRevenueValues,
                                borderColor: '#10B981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                pointBackgroundColor: '#10B981',
                                pointBorderColor: '#0f172a',
                                pointRadius: 5,
                                pointHoverRadius: 7,
                                borderWidth: 3,
                                tension: 0.4,
                                fill: true,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: {
                                intersect: false,
                                mode: 'index',
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: '#1e293b',
                                    titleColor: '#f8fafc',
                                    bodyColor: '#cbd5e1',
                                    borderColor: '#334155',
                                    borderWidth: 1,
                                    padding: 12,
                                    displayColors: false,
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.dataset.label || '';
                                            if (label) {
                                                label += ': ';
                                            }
                                            if (context.parsed.y !== null) {
                                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'MAD' }).format(context.parsed.y);
                                            }
                                            return label;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        color: '#64748b',
                                        font: {
                                            family: "'Outfit', sans-serif",
                                            size: 12
                                        }
                                    },
                                    grid: {
                                        display: false,
                                        drawBorder: false
                                    }
                                },
                                y: {
                                    ticks: {
                                        color: '#64748b',
                                        font: {
                                            family: "'Outfit', sans-serif",
                                            size: 12
                                        },
                                        padding: 10,
                                        callback: function(value) {
                                            return value + ' MAD';
                                        }
                                    },
                                    grid: {
                                        color: '#1e293b',
                                        drawBorder: false,
                                        borderDash: [5, 5]
                                    }
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endif
</x-dashboard.layout>
