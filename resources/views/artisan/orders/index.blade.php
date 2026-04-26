@php
    $sidebarItems = \App\View\ArtisanSidebar::items('orders', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout header-title="Orders">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-6">
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">Order Management</h1>
                <p class="mt-1 text-sm text-slate-400">View and manage all customer orders.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="#" class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-slate-700">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Orders
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <!-- Stat Card 1 -->
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-amber-500/80 mb-1">Pending Orders</p>
                <p class="text-3xl font-bold text-white">{{ $totalPending }}</p>
            </div>
            
            <!-- Stat Card 2 -->
            <div class="rounded-xl border border-blue-500/20 bg-blue-500/5 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-blue-500/80 mb-1">Processing</p>
                <p class="text-3xl font-bold text-white">{{ $totalProcessing }}</p>
            </div>
            
            <!-- Stat Card 3 -->
            <div class="rounded-xl border border-[#10B981]/20 bg-[#10B981]/5 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-[#10B981]/80 mb-1">Shipped</p>
                <p class="text-3xl font-bold text-white">{{ $totalShipped }}</p>
            </div>
            
            <!-- Stat Card 4 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-slate-400 mb-1">Total Orders</p>
                <p class="text-3xl font-bold text-white">{{ $orders->total() }}</p>
            </div>
        </div>

        <!-- Orders Table/List -->
        <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 shadow-sm backdrop-blur-sm">
            <div class="flex flex-col border-b border-slate-800 p-6 sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-lg font-semibold text-white">All Orders</h2>
                <div class="w-full sm:w-64">
                    <input type="text" placeholder="Search by order ID or customer..." class="w-full rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-2 text-sm text-white placeholder-slate-500 focus:border-[#10B981] focus:outline-none focus:ring-1 focus:ring-[#10B981]">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900/50 text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-medium">Order ID</th>
                            <th class="px-6 py-4 font-medium">Customer</th>
                            <th class="px-6 py-4 font-medium">Date</th>
                            <th class="px-6 py-4 font-medium">Status</th>
                            <th class="px-6 py-4 font-medium text-right">Amount</th>
                            <th class="px-6 py-4 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse ($orders as $order)
                            @php
                                $statusStyles = match($order->status->value) {
                                    'pending' => 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
                                    'processing' => 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
                                    'shipped', 'delivered' => 'bg-[#10B981]/10 text-[#10B981] ring-[#10B981]/20',
                                    'cancelled' => 'bg-rose-500/10 text-rose-400 ring-rose-500/20',
                                    default => 'bg-slate-500/10 text-slate-400 ring-slate-500/20',
                                };
                            @endphp
                            <tr class="transition hover:bg-slate-800/30">
                                <td class="px-6 py-4 font-medium text-white">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800">
                                            <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                        </div>
                                        <div>
                                            #{{ $order->id }}
                                            <div class="text-xs text-slate-400 font-normal mt-0.5">{{ $order->items->sum('quantity') }} items</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-slate-300 font-medium">{{ $order->user?->name ?? 'Unknown buyer' }}</td>
                                <td class="px-6 py-4 text-slate-400">{{ $order->created_at?->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $statusStyles }}">
                                        {{ ucfirst($order->status->value) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-white">{{ number_format((float) $order->total_amount, 2) }} MAD</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('artisan.orders.show', $order) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/50 px-3 py-1.5 text-sm font-medium text-slate-300 transition hover:bg-slate-700 hover:text-white">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="inline-flex items-center justify-center rounded-full bg-slate-800/50 p-4 mb-4">
                                        <svg class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-white mb-1">No orders yet</h3>
                                    <p class="text-sm text-slate-400">When customers place orders, they will appear here.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="border-t border-slate-800 p-4">
                    {{ $orders->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard.layout>
