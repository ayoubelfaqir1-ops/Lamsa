@php
    $sidebarItems = \App\View\ArtisanSidebar::items('auctions', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout header-title="Auctions">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-400 shadow-sm backdrop-blur-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">Auction Management</h1>
                <p class="mt-1 text-sm text-slate-400">Track live lots, scheduled launches, and bidding activity.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('artisan.products') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-sm font-medium text-slate-300 shadow-sm transition hover:bg-slate-700 hover:text-white">
                    Manage Products
                </a>
                <a href="{{ route('artisan.auctions.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#10B981] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#059669] focus:outline-none focus:ring-2 focus:ring-[#10B981]/50">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Auction
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-5">
            <div class="rounded-xl border border-blue-500/20 bg-blue-500/5 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-blue-500/80 mb-1">Live Auctions</p>
                <p class="text-3xl font-bold text-white">{{ $summary['liveAuctions'] }}</p>
            </div>
            <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-amber-500/80 mb-1">Scheduled</p>
                <p class="text-3xl font-bold text-white">{{ $summary['scheduledAuctions'] }}</p>
            </div>
            <div class="rounded-xl border border-[#10B981]/20 bg-[#10B981]/5 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-[#10B981]/80 mb-1">Ended</p>
                <p class="text-3xl font-bold text-white">{{ $summary['endedAuctions'] }}</p>
            </div>
            <div class="rounded-xl border border-rose-500/20 bg-rose-500/5 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-rose-500/80 mb-1">Cancelled</p>
                <p class="text-3xl font-bold text-white">{{ $summary['cancelledAuctions'] }}</p>
            </div>
            <div class="col-span-2 lg:col-span-1 rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <p class="text-sm font-medium text-slate-400 mb-1">Total Bids</p>
                <p class="text-3xl font-bold text-white">{{ $summary['totalBids'] }}</p>
            </div>
        </div>

        <!-- Auctions List -->
        <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 shadow-sm backdrop-blur-sm">
            <div class="flex flex-col border-b border-slate-800 p-6 sm:flex-row sm:items-center sm:justify-between gap-4">
                <h2 class="text-lg font-semibold text-white">All Auctions</h2>
                <div class="w-full sm:w-64">
                    <input type="text" placeholder="Search auctions..." class="w-full rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-2 text-sm text-white placeholder-slate-500 focus:border-[#10B981] focus:outline-none focus:ring-1 focus:ring-[#10B981]">
                </div>
            </div>

            <div class="divide-y divide-slate-800/50">
                @forelse ($auctions as $auction)
                    @php
                        $isScheduled = $auction->status->value === 'active' && $auction->starts_at?->isFuture();
                        $isLive = $auction->status->value === 'active' && $auction->starts_at?->isPast() && $auction->ends_at?->isFuture();

                        $badgeClasses = match (true) {
                            $isLive => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
                            $isScheduled => 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
                            $auction->status->value === 'ended' => 'bg-slate-500/10 text-slate-400 ring-slate-500/20',
                            $auction->status->value === 'cancelled' => 'bg-rose-500/10 text-rose-400 ring-rose-500/20',
                            default => 'bg-slate-500/10 text-slate-400 ring-slate-500/20',
                        };

                        $statusLabel = match (true) {
                            $isLive => 'Live',
                            $isScheduled => 'Scheduled',
                            default => str($auction->status->value)->headline(),
                        };

                        $canEdit = ! $auction->bids_count && $auction->starts_at?->isFuture();
                    @endphp

                    <div class="p-6 transition hover:bg-slate-800/30">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1 space-y-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-base font-semibold text-white">
                                            {{ $auction->name ?? 'Untitled auction' }}
                                        </h3>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $badgeClasses }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-slate-400">
                                        <span>{{ $auction->starts_at?->format('M d, Y H:i') }} &mdash; {{ $auction->ends_at?->format('M d, Y H:i') }}</span>
                                        <span>&bull;</span>
                                        <span class="{{ $auction->is_published ? 'text-emerald-400' : 'text-slate-500' }}">
                                            {{ $auction->is_published ? 'Published' : 'Hidden' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                    <div>
                                        <p class="text-sm font-medium text-slate-400">Current Price</p>
                                        <p class="mt-1 text-lg font-bold text-white">{{ number_format((float) $auction->current_price, 2) }} MAD</p>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-400">Reserve Price</p>
                                        <p class="mt-1 text-lg font-bold text-white">{{ number_format((float) $auction->reserve_price, 2) }} MAD</p>
                                    </div>
                                    <div class="col-span-2 sm:col-span-1">
                                        <p class="text-sm font-medium text-slate-400">Bid Count</p>
                                        <p class="mt-1 text-lg font-bold text-white">{{ $auction->bids_count }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="w-full lg:w-72 lg:shrink-0 space-y-4">
                                <div class="rounded-lg bg-slate-900/50 p-4 border border-slate-800">
                                    <p class="text-sm font-medium text-slate-400 mb-2">Highest Bidder</p>
                                    @if ($auction->highestBid)
                                        <div class="flex items-center justify-between">
                                            <p class="text-sm font-medium text-white">
                                                {{ $auction->highestBid->user?->name ?? 'Unknown bidder' }}
                                            </p>
                                            <p class="text-sm font-bold text-[#10B981]">
                                                {{ number_format((float) $auction->highestBid->amount, 2) }} MAD
                                            </p>
                                        </div>
                                    @else
                                        <p class="text-sm text-slate-500 italic">No bids received yet</p>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @if ($canEdit)
                                        <a href="{{ route('artisan.auctions.edit', $auction) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/50 px-3 py-1.5 text-sm font-medium text-slate-300 transition hover:bg-slate-700 hover:text-white">
                                            Edit
                                        </a>
                                    @endif

                                    <form action="{{ route('artisan.auctions.toggle-publish', $auction) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border px-3 py-1.5 text-sm font-medium transition {{ $auction->is_published ? 'border-amber-500/20 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20' : 'border-[#10B981]/20 bg-[#10B981]/10 text-[#10B981] hover:bg-[#10B981]/20' }}">
                                            {{ $auction->is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>

                                    @if ($auction->status->value === 'active')
                                        <form action="{{ route('artisan.auctions.cancel', $auction) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-rose-500/20 bg-rose-500/10 px-3 py-1.5 text-sm font-medium text-rose-400 transition hover:bg-rose-500/20">
                                                Cancel
                                            </button>
                                        </form>
                                    @endif

                                    @if (! $auction->bids_count)
                                        <form action="{{ route('artisan.auctions.destroy', $auction) }}" method="POST" onsubmit="return confirm('Delete this auction permanently?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-slate-700 px-3 py-1.5 text-sm font-medium text-slate-300 transition hover:bg-slate-700 hover:text-white">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-20">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-800/50">
                            <svg class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-white">No auctions yet</h3>
                        <p class="mt-1 text-sm text-slate-400">Get started by launching your first auction.</p>
                        <a href="{{ route('artisan.auctions.create') }}" class="mt-6 inline-flex items-center rounded-lg bg-[#10B981] px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                            Launch First Auction
                        </a>
                    </div>
                @endforelse
            </div>

            @if ($auctions->hasPages())
                <div class="border-t border-slate-800 p-4">
                    {{ $auctions->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard.layout>
