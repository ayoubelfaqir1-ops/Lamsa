@php
    $sidebarItems = \App\View\ArtisanSidebar::items('store', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout header-title="Create Store">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="mx-auto max-w-4xl space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-400 shadow-sm backdrop-blur-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight text-white">
                Create Your Store
            </h1>
            <p class="text-sm text-slate-400">
                Set up your artisan storefront first, then you can start adding products to it.
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-6 shadow-sm backdrop-blur-sm md:p-8">
            @include('artisan.store._form', [
                'action' => route('artisan.store.store'),
                'store' => null,
                'method' => 'POST',
                'submitLabel' => 'Create Store',
                'backUrl' => route('artisan.dashboard'),
                'backLabel' => 'Back to Dashboard',
            ])
        </div>
    </div>
</x-dashboard.layout>
