@php
    use App\Enums\ProductStatus;

    $sidebarItems = \App\View\ArtisanSidebar::items('products', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout header-title="Products">
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
                <h1 class="text-2xl font-semibold tracking-tight text-white">Product Inventory</h1>
                <p class="mt-1 text-sm text-slate-400">Manage your catalog, prices, and stock from one place.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('artisan.products.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[#10B981] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[#059669] focus:outline-none focus:ring-2 focus:ring-[#10B981]/50">
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add New Product
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Stat Card 1 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-500/10 text-blue-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Total Products</p>
                        <p class="text-2xl font-bold text-white">{{ $totalProducts }}</p>
                    </div>
                </div>
            </div>

            <!-- Stat Card 2 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Active Listings</p>
                        <p class="text-2xl font-bold text-white">{{ $totalActive }}</p>
                    </div>
                </div>
            </div>

            <!-- Stat Card 3 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-500/10 text-purple-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Published</p>
                        <p class="text-2xl font-bold text-white">{{ $publishedProducts }}</p>
                    </div>
                </div>
            </div>

            <!-- Stat Card 4 -->
            <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 p-5 shadow-sm backdrop-blur-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-amber-500/10 text-amber-400">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-400">Units Sold</p>
                        <p class="text-2xl font-bold text-white">{{ $totalUnitsSold }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory List -->
        <div class="rounded-xl border border-slate-800 bg-[#0f172a]/80 shadow-sm backdrop-blur-sm">
            <div class="flex flex-col border-b border-slate-800 p-6 sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-white">All Products</h2>
                    <p class="mt-1 text-sm text-slate-400">{{ $totalPending }} pending &middot; {{ $totalSuspended }} suspended &middot; {{ $totalInactive }} inactive</p>
                </div>
                <div class="w-full sm:w-64">
                    <input type="text" placeholder="Search products..." class="w-full rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-2 text-sm text-white placeholder-slate-500 focus:border-[#10B981] focus:outline-none focus:ring-1 focus:ring-[#10B981]">
                </div>
            </div>

            <div class="divide-y divide-slate-800/50">
                @forelse ($products as $product)
                    @php
                        $statusStyles = match ($product->status) {
                            ProductStatus::Active => 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/20',
                            ProductStatus::Pending => 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
                            ProductStatus::Suspended => 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
                            default => 'bg-slate-500/10 text-slate-400 ring-slate-500/20',
                        };
                        $productImage = collect($product->images ?? [])
                            ->filter()
                            ->map(fn ($image) => str_starts_with($image, 'http') ? $image : Storage::url($image))
                            ->first();
                    @endphp

                    <div class="p-6 transition hover:bg-slate-800/30">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="flex min-w-0 flex-1 items-start gap-4">
                                <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg border border-slate-800 bg-slate-900 flex items-center justify-center text-lg font-bold text-slate-500">
                                    @if($productImage)
                                        <img src="{{ $productImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        {{ \Illuminate\Support\Str::substr($product->name, 0, 1) }}
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex items-center gap-3">
                                        <h3 class="truncate text-base font-semibold text-white">{{ $product->name }}</h3>
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $statusStyles }}">
                                            {{ ucfirst($product->status->value) }}
                                        </span>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                                        <span>{{ $product->category?->name ?? 'Unassigned' }}</span>
                                        <span>&bull;</span>
                                        <span class="font-medium text-white">{{ number_format((float) $product->price, 2) }} MAD</span>
                                        <span>&bull;</span>
                                        <span class="{{ $product->stock < 5 ? 'text-amber-400' : '' }}">Stock: {{ $product->stock }}</span>
                                    </div>

                                    <p class="mt-2 text-sm text-slate-400 line-clamp-2">
                                        {{ strip_tags($product->description ?: 'No description available for this product yet.') }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col gap-3 sm:flex-row lg:flex-col lg:items-end">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('artisan.products.edit', $product) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-700 bg-slate-800/50 px-3 py-1.5 text-sm font-medium text-slate-300 transition hover:bg-slate-700 hover:text-white">
                                        Edit
                                    </a>
                                    <form action="{{ route('artisan.products.toggle-publish', $product) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center justify-center rounded-lg border px-3 py-1.5 text-sm font-medium transition {{ $product->is_published ? 'border-amber-500/20 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20' : 'border-[#10B981]/20 bg-[#10B981]/10 text-[#10B981] hover:bg-[#10B981]/20' }}">
                                            {{ $product->is_published ? 'Unpublish' : 'Publish' }}
                                        </button>
                                    </form>
                                </div>
                                
                                <div class="flex items-center gap-4 text-sm text-slate-400">
                                    <div class="flex items-center gap-1" title="Reviews">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                                        {{ number_format((float) ($product->reviews_avg_rating ?? 0), 1) }} ({{ $product->reviews_count }})
                                    </div>
                                    <div class="flex items-center gap-1" title="Units Sold">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        {{ (int) ($product->total_units_sold ?? 0) }} sold
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-20">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-800/50">
                            <svg class="h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-white">No products yet</h3>
                        <p class="mt-1 text-sm text-slate-400">Get started by creating your first product listing.</p>
                        <a href="{{ route('artisan.products.create') }}" class="mt-6 inline-flex items-center rounded-lg bg-[#10B981] px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500">
                            Add New Product
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-dashboard.layout>
