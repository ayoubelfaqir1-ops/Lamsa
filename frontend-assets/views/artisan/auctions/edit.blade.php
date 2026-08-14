@php
    $sidebarItems = \App\View\ArtisanSidebar::items('auctions', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout :header-title="$auction->name">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="mx-auto max-w-4xl space-y-8">
        <div class="space-y-3">
            <h1 class="text-4xl font-light uppercase tracking-[0.25em] text-white">
                Edit <span class="font-black italic text-amber-300">Auction</span>
            </h1>
            <p class="text-xs font-black uppercase tracking-widest text-slate-300">
                Adjust the listing story, schedule, reserve, and marketplace visibility before the auction begins.
            </p>
        </div>

        <div class="border border-slate-700 bg-[#182235] p-6 shadow-[0_20px_60px_rgba(15,23,42,0.18)] md:p-8 xl:p-10">
            @include('artisan.auctions._form', [
                'action' => route('artisan.auctions.update', $auction),
                'submitLabel' => 'Save Changes',
                'auction' => $auction,
                'method' => 'PATCH',
                'categories' => $categories,
            ])
        </div>
    </div>
</x-dashboard.layout>
