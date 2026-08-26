@php
    $sidebarItems = [
        ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Artisans', 'href' => route('admin.artisans'), 'icon' => 'users'],
        ['label' => 'Categories', 'href' => route('admin.categories'), 'icon' => 'menu', 'active' => true],
    ];
@endphp

<x-dashboard.layout :header-title="$category->name">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-10">
        @if (session('success'))
            <div class="border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-emerald-400">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('delete'))
            <div class="border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-rose-400">
                {{ $errors->first('delete') }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 2xl:grid-cols-[minmax(0,1fr)_auto] 2xl:items-start">
            <div class="space-y-3 min-w-0">
                <h1 class="text-4xl font-light uppercase tracking-[0.25em] text-white">
                    {{ $category->name }} <span class="font-black italic text-[#10B981]">Detail</span>
                </h1>
                <div class="flex flex-wrap items-center gap-4 text-[10px] font-black uppercase tracking-widest text-slate-300">
                    <span>Slug / {{ $category->slug }}</span>
                    <span>Status / {{ $category->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row 2xl:justify-end">
                <form action="{{ route('admin.categories.toggle-active', $category) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="w-full border px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] transition-all sm:w-auto {{ $category->is_active ? 'border-amber-500/30 bg-amber-500/5 text-amber-300 hover:border-amber-400 hover:bg-amber-500/10 hover:text-white' : 'border-emerald-500/30 bg-emerald-500/5 text-emerald-300 hover:border-emerald-400 hover:bg-emerald-500/10 hover:text-white' }}">
                        {{ $category->is_active ? 'Deactivate Category' : 'Activate Category' }}
                    </button>
                </form>

                <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Delete this category? This only works when it has no products linked to it.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full border border-rose-500/30 px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-rose-300 transition-all hover:border-rose-400 hover:bg-rose-500/10 hover:text-white sm:w-auto">
                        Delete Category
                    </button>
                </form>

                <a href="{{ route('admin.categories.create') }}" class="inline-flex w-full items-center justify-center bg-[#10B981] px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-emerald-400 sm:w-auto">
                    Create Another
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 items-start gap-8 2xl:grid-cols-[minmax(280px,0.72fr)_minmax(0,1.28fr)]">
            <section class="space-y-8">
                <div class="grid grid-cols-1 gap-4">
                    <x-dashboard.stat-card label="Products" :value="$category->products_count" />
                </div>

                <div class="border border-slate-700 bg-[#182235] p-8 shadow-[0_16px_40px_rgba(15,23,42,0.14)]">
                    <div class="mb-6 space-y-2 border-b border-slate-700 pb-5">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Category Summary</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-300">A quick read of the current taxonomy node</p>
                    </div>

                    <div class="space-y-4 text-sm leading-relaxed text-slate-200">
                        <p>{{ $category->description ?: 'No description has been written for this category yet.' }}</p>
                        <p>Keep names broad enough to group related products, but specific enough to make browsing and filtering intuitive.</p>
                    </div>
                </div>
            </section>

            <section class="min-w-0 space-y-8">
                <div class="border border-slate-700 bg-[#182235] p-8 shadow-[0_16px_40px_rgba(15,23,42,0.14)]">
                    <div class="mb-6 space-y-2 border-b border-slate-700 pb-5">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Edit Category</h2>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-300">Update naming, hierarchy, and visibility rules</p>
                    </div>

                    @include('admin.categories._form', [
                        'action' => route('admin.categories.update', $category),
                        'method' => 'PATCH',
                        'submitLabel' => 'Save Changes',
                        'category' => $category,
                    ])
                </div>
            </section>
        </div>
    </div>
</x-dashboard.layout>
