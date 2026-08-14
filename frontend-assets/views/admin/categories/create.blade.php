@php
    $sidebarItems = [
        ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Artisans', 'href' => route('admin.artisans'), 'icon' => 'users'],
        ['label' => 'Categories', 'href' => route('admin.categories'), 'icon' => 'menu', 'active' => true],
    ];
@endphp

<x-dashboard.layout header-title="Create Category">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="mx-auto max-w-4xl space-y-8">
        <div class="space-y-3">
            <h1 class="text-4xl font-light uppercase tracking-[0.25em] text-white">
                New <span class="font-black italic text-[#10B981]">Category</span>
            </h1>
            <p class="text-xs font-black uppercase tracking-widest text-slate-300">
                Start with the core identity and publish a clean, flat category list for the catalog.
            </p>
        </div>

        <div class="border border-slate-700 bg-[#182235] p-6 shadow-[0_20px_60px_rgba(15,23,42,0.18)] md:p-8 xl:p-10">
            @include('admin.categories._form', [
                'action' => route('admin.categories.store'),
                'submitLabel' => 'Create Category',
                'category' => null,
                'method' => 'POST',
            ])
        </div>
    </div>
</x-dashboard.layout>
