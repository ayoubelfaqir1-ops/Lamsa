@php
    $sidebarItems = \App\View\ArtisanSidebar::items('store', $store);
@endphp

<x-dashboard.layout :header-title="$store->name">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-400 shadow-sm backdrop-blur-sm">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight text-white">Storefront Preview</h1>
                <p class="mt-1 text-sm text-slate-400">View and manage your public brand identity.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('artisan.store.edit', $store) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2.5 text-sm font-medium text-slate-300 shadow-sm transition hover:bg-slate-700 hover:text-white">
                    Edit Settings
                </a>
                <a href="{{ url('/' . $store->slug) }}" target="_blank" class="inline-flex items-center justify-center rounded-lg bg-[#10B981] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#059669] focus:outline-none focus:ring-2 focus:ring-[#10B981]/50">
                    <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    View Public Store
                </a>
            </div>
        </div>

        <section class="overflow-hidden rounded-xl border border-slate-800 bg-[#0f172a]/80 shadow-sm backdrop-blur-sm">
            <div class="relative border-b border-slate-800 px-6 py-8 sm:px-8 xl:px-10">
                <div class="relative grid gap-8 xl:grid-cols-[minmax(0,1.5fr)_1fr] xl:items-start">
                    <div class="space-y-6">
                        <div class="flex items-start gap-5">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-700 bg-slate-900 shadow-inner">
                                @if ($store->logo)
                                    <img
                                        src="{{ asset('storage/' . $store->logo) }}"
                                        alt="{{ $store->name }} logo"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <span class="text-2xl font-bold text-slate-600">
                                        {{ \Illuminate\Support\Str::substr($store->name, 0, 2) }}
                                    </span>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <h2 class="text-3xl font-bold text-white tracking-tight">
                                    {{ $store->name }}
                                </h2>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $store->is_active ? 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20' : 'bg-slate-500/10 text-slate-400 ring-slate-500/20' }}">
                                        {{ $store->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                    <span class="text-sm text-slate-400">
                                        Member since {{ $store->created_at?->format('M Y') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="prose prose-sm prose-invert max-w-none text-slate-300">
                            @if ($store->description)
                                <p class="text-base leading-relaxed">{{ $store->description }}</p>
                            @else
                                <div class="rounded-lg border border-dashed border-slate-700 bg-slate-800/30 p-4 text-center">
                                    <p class="text-sm text-slate-400">This storefront is ready, but it still needs a stronger story. Add a warm description so buyers immediately understand your craft and style.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 xl:ml-auto xl:w-full">
                        <div class="col-span-2 rounded-lg border border-slate-700 bg-slate-900/50 p-5">
                            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Store Checklist</p>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-300">Brand Story</span>
                                    @if ($store->description)
                                        <span class="text-emerald-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                                    @else
                                        <span class="text-amber-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-300">Visual Identity</span>
                                    @if ($store->logo)
                                        <span class="text-emerald-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                                    @else
                                        <span class="text-amber-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg></span>
                                    @endif
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-300">Store Status</span>
                                    @if ($store->is_active)
                                        <span class="text-emerald-400"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></span>
                                    @else
                                        <span class="text-slate-500"><svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 bg-slate-900/30 p-6 sm:p-8 lg:grid-cols-2">
                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-white">Public Details</h3>
                    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4 space-y-4">
                        <div>
                            <p class="text-xs font-medium text-slate-400 mb-1">Public Link</p>
                            <p class="text-sm text-emerald-400 break-all font-mono">lamsa.com/{{ $store->slug }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-medium text-slate-400 mb-1">Created</p>
                            <p class="text-sm text-white">{{ $store->created_at?->format('F d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="text-sm font-semibold text-white">Quick Actions</h3>
                    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4 space-y-3">
                        <a href="{{ route('artisan.products.create') }}" class="flex w-full items-center justify-between rounded-md bg-slate-700/50 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700">
                            <span>Add New Product</span>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('artisan.store.edit', $store) }}" class="flex w-full items-center justify-between rounded-md bg-slate-700/50 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700">
                            <span>Update Branding</span>
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-dashboard.layout>
