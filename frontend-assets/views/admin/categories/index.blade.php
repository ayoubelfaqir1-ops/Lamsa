@php
    $sidebarItems = [
        ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Artisans', 'href' => route('admin.artisans'), 'icon' => 'users'],
        ['label' => 'Categories', 'href' => route('admin.categories'), 'icon' => 'menu', 'active' => true],
    ];
@endphp

<x-dashboard.layout header-title="Category Atlas">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-12">
        @if (session('success'))
            <div class="border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-emerald-400">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-8 2xl:grid-cols-[minmax(0,1fr)_360px] 2xl:items-start">
            <div class="space-y-6">
                <h1 class="text-4xl font-light uppercase tracking-[0.25em] text-white">
                    Category <span class="font-black italic text-[#10B981]">Atlas</span>
                </h1>
                <p class="text-xs font-black uppercase tracking-widest text-[#10B981]/60">
                    Structure, visibility, and product coverage for your catalog taxonomy
                </p>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <x-dashboard.stat-card label="Total Categories" :value="$summary['totalCategories']" />
                    <x-dashboard.stat-card label="Active" :value="$summary['activeCategories']" />
                    <x-dashboard.stat-card label="Linked Products" :value="$summary['linkedProducts']" />
                </div>
            </div>

            <div class="flex items-start 2xl:justify-end">
                <a href="{{ route('admin.categories.create') }}" class="inline-flex w-full items-center justify-center bg-[#10B981] px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-emerald-400 2xl:w-auto">
                    Create Category
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-8 2xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,0.7fr)]">
            <section class="min-w-0 border border-slate-700 bg-[#182235] p-6 shadow-[0_20px_60px_rgba(15,23,42,0.18)] md:p-8 xl:p-10">
                <div class="mb-8 flex items-center justify-between border-b border-slate-700 pb-6">
                    <div class="space-y-2">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Category Registry</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Recent taxonomy entries with hierarchy context</p>
                    </div>
                    <div class="text-[9px] font-black uppercase tracking-widest text-slate-400">
                        {{ $categories->total() }} entries
                    </div>
                </div>

                <div class="space-y-4">
                    @forelse ($categories as $category)
                        <article class="group border border-slate-700 bg-[#243247] p-6 shadow-sm transition-all hover:border-slate-500 hover:bg-[#2a3950]">
                            <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0 flex items-start gap-5">
                                    <div class="flex h-14 w-14 shrink-0 items-center justify-center border border-slate-600 bg-slate-800 text-xs font-black uppercase tracking-widest text-white transition-all group-hover:border-[#10B981] group-hover:bg-[#10B981]/10">
                                        {{ substr($category->name, 0, 2) }}
                                    </div>

                                    <div class="min-w-0 space-y-2">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h3 class="text-sm font-black uppercase tracking-[0.18em] text-white">
                                                {{ $category->name }}
                                            </h3>
                                            <span class="border px-3 py-1 text-[8px] font-black uppercase tracking-widest {{ $category->is_active ? 'border-emerald-500/20 bg-emerald-500/5 text-emerald-400' : 'border-rose-500/20 bg-rose-500/5 text-rose-400' }}">
                                                {{ $category->is_active ? 'active' : 'hidden' }}
                                            </span>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 text-[10px] font-bold uppercase tracking-widest text-slate-300">
                                            <span>Slug / {{ $category->slug }}</span>
                                            <span>Created / {{ $category->created_at->format('d M Y') }}</span>
                                        </div>

                                        @if ($category->description)
                                            <p class="max-w-3xl text-sm leading-relaxed text-slate-200">
                                                {{ $category->description }}
                                            </p>
                                        @else
                                            <p class="text-sm italic text-slate-300">
                                                No description has been added for this category yet.
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="grid w-full grid-cols-1 gap-3 sm:max-w-[240px] xl:w-[240px]">
                                    <div class="border border-slate-600 bg-slate-800/70 px-4 py-3">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-300">Products</p>
                                        <p class="mt-2 text-2xl font-light text-white">{{ $category->products_count }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-col gap-3 border-t border-slate-700 pt-5 sm:flex-row">
                                <a href="{{ route('admin.categories.show', $category) }}" class="inline-flex items-center justify-center bg-slate-800 px-4 py-3 text-[10px] font-black uppercase tracking-[0.18em] text-white transition-all hover:bg-slate-700">
                                    Details
                                </a>

                                <form action="{{ route('admin.categories.toggle-active', $category) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="w-full border px-4 py-3 text-[10px] font-black uppercase tracking-[0.18em] transition-all sm:w-auto {{ $category->is_active ? 'border-amber-500/30 bg-amber-500/5 text-amber-300 hover:border-amber-400 hover:bg-amber-500/10 hover:text-white' : 'border-emerald-500/30 bg-emerald-500/5 text-emerald-300 hover:border-emerald-400 hover:bg-emerald-500/10 hover:text-white' }}">
                                        {{ $category->is_active ? 'Deactivate Category' : 'Activate Category' }}
                                    </button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <div class="border border-dashed border-slate-700 bg-slate-800/30 py-24 text-center">
                            <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-300">
                                No categories available yet
                            </p>
                        </div>
                    @endforelse
                </div>

                <div class="mt-8">
                    {{ $categories->links(data: ['scrollTo' => false]) }}
                </div>
            </section>

            <aside class="space-y-8">
                <section class="border border-slate-700 bg-[#182235] p-8 shadow-[0_16px_40px_rgba(15,23,42,0.14)]">
                    <div class="mb-6 space-y-2 border-b border-slate-700 pb-5">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Admin Notes</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Quick reading guide for taxonomy health</p>
                    </div>

                    <div class="space-y-4 text-sm leading-relaxed text-slate-200">
                        <p>
                            Category names should stay clear and broad enough to support clean browsing and filtering across the catalog.
                        </p>
                        <p>
                            Product counts show which branches are actually being used. A category with no products may need content, merging, or archival.
                        </p>
                        <p>
                            Slugs are the public URL keys. They should stay readable and stable once a category is in use.
                        </p>
                    </div>
                </section>

                <section class="border border-slate-700 bg-[#243247] p-8 shadow-[0_16px_40px_rgba(15,23,42,0.14)]">
                    <div class="mb-6 flex items-center justify-between border-b border-slate-600 pb-5">
                        <div class="space-y-2">
                            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Visibility Split</h2>
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-300">Current publication state</p>
                        </div>
                    </div>

                    @php
                        $inactiveCategories = $summary['totalCategories'] - $summary['activeCategories'];
                        $activeRatio = $summary['totalCategories'] > 0
                            ? round(($summary['activeCategories'] / $summary['totalCategories']) * 100)
                            : 0;
                    @endphp

                    <div class="space-y-5">
                        <div class="h-3 overflow-hidden bg-slate-800">
                            <div class="h-full bg-[#10B981]" style="width: {{ $activeRatio }}%"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="border border-slate-600 bg-slate-800/60 p-4">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-300">Active Ratio</p>
                                <p class="mt-2 text-2xl font-light text-white">{{ $activeRatio }}%</p>
                            </div>
                            <div class="border border-slate-600 bg-slate-800/60 p-4">
                                <p class="text-[8px] font-black uppercase tracking-widest text-slate-300">Inactive</p>
                                <p class="mt-2 text-2xl font-light text-white">{{ $inactiveCategories }}</p>
                            </div>
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </div>
</x-dashboard.layout>
