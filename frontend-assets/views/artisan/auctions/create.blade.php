@php
    $sidebarItems = \App\View\ArtisanSidebar::items('auctions', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout header-title="Create Auction">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="mx-auto max-w-4xl space-y-8">
        <div class="space-y-3">
            <h1 class="text-4xl font-light uppercase tracking-[0.25em] text-white">
                New <span class="font-black italic text-amber-300">Auction</span>
            </h1>
            <p class="text-xs font-black uppercase tracking-widest text-slate-300">
                Launch a standalone auction with its own timeline, reserve, and marketplace visibility.
            </p>
        </div>

        <div class="border border-slate-700 bg-[#182235] p-6 shadow-[0_20px_60px_rgba(15,23,42,0.18)] md:p-8 xl:p-10">
            @include('artisan.auctions._form', [
                'action' => route('artisan.auctions.store'),
                'submitLabel' => 'Create Auction',
                'auction' => null,
                'method' => 'POST',
                'categories' => $categories,
            ])
        </div>
    </div>
</x-dashboard.layout>
