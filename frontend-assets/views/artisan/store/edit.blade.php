@php
    $sidebarItems = \App\View\ArtisanSidebar::items('store', $store);
@endphp

<x-dashboard.layout :header-title="$store->name">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="mx-auto max-w-4xl space-y-6">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold tracking-tight text-white">
                Edit Store Settings
            </h1>
            <p class="text-sm text-slate-400">
                Update your storefront branding, description, and public identity.
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-6 shadow-sm backdrop-blur-sm md:p-8">
            @include('artisan.store._form', [
                'action' => route('artisan.store.update', $store),
                'store' => $store,
                'method' => 'PATCH',
                'submitLabel' => 'Save Changes',
                'backUrl' => route('artisan.store.show', $store),
                'backLabel' => 'Back to Store',
            ])
        </div>
    </div>
</x-dashboard.layout>
