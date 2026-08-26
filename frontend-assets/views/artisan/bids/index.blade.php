@php
    $sidebarItems = \App\View\ArtisanSidebar::items('bids', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout header-title="Auction Bids">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">Received Bids</h1>
                <p class="mt-1 text-sm text-slate-400">Review all offers placed on your auctions and monitor bidder activity in one place.</p>
            </div>
            <a href="{{ route('artisan.auctions') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-sm font-medium text-slate-300 transition hover:bg-slate-700 hover:text-white">
                Back to Auctions
            </a>
        </div>

        <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 shadow-sm backdrop-blur-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-900/50 text-slate-400">
                        <tr>
                            <th class="px-6 py-4 font-medium">Auction</th>
                            <th class="px-6 py-4 font-medium">Bidder</th>
                            <th class="px-6 py-4 font-medium">Placed</th>
                            <th class="px-6 py-4 text-right font-medium">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50">
                        @forelse ($bids as $bid)
                            <tr class="transition hover:bg-slate-800/30">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-white">{{ $bid->auction?->name ?? 'Auction item' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $bid->auction?->ends_at?->diffForHumans() ? 'Ends '.$bid->auction->ends_at->diffForHumans() : 'Schedule unavailable' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-slate-200">{{ $bid->user?->name ?? 'Unknown bidder' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $bid->user?->email ?? 'No email available' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-300">{{ $bid->created_at->diffForHumans() }}</td>
                                <td class="px-6 py-4 text-right font-semibold text-amber-400">{{ number_format((float) $bid->amount, 2) }} MAD</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                    No bids have been placed on your auctions yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($bids->hasPages())
                <div class="border-t border-slate-800 px-6 py-4">
                    {{ $bids->links('components.pagination') }}
                </div>
            @endif
        </div>
    </div>
</x-dashboard.layout>
