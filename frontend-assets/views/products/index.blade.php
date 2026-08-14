<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Lamsa - Products') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
            .category-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .category-scrollbar::-webkit-scrollbar {
                display: none;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white bg-[#FAFAFA] overflow-x-hidden">
        <x-navbar />
        <main class="pt-[70px]">
            <section class="bg-white">
                <div class="mx-auto max-w-7xl px-6 py-6 md:px-12 md:py-8">
                    <div class="text-center">
                        <h2 class="text-2xl md:text-4xl font-light tracking-tight text-black">
                            Art & Collectibles
                        </h2>
                        <p class="mx-auto mt-3 max-w-3xl text-sm md:text-base text-gray-500 leading-relaxed">
                            Discover handcrafted Moroccan artwork, decorative pieces, and one-of-a-kind objects curated to turn your home into a gallery.
                        </p>
                    </div>

                    <div class="category-scrollbar mt-6 overflow-x-auto pb-2">

                        <div class="flex min-w-max snap-x gap-3 sm:gap-4 xl:gap-5">
                            @foreach ($categories as $category)
                                <a
                                    href="{{ route('products.index', ['category'=>$category->slug])}}"
                                    class="group block w-[220px] shrink-0 snap-start focus:outline-none md:w-[230px] xl:w-[240px]"
                                >
                                    <div class="aspect-[4/5] overflow-hidden border border-gray-100 bg-[#F1F1F1] transition-shadow duration-300 {{ $category->slug == $selectedCategory ? 'ring-2 ring-black ring-offset-2 ring-offset-white' : 'group-hover:shadow-[0_14px_30px_rgba(0,0,0,0.08)]' }}">
                                        <img src="{{ $category->image }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                                    </div>
                                    <p class="mt-3 text-center text-[12px] font-bold text-black md:text-[14px]">
                                        {{ $category->name }}
                                    </p>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class=" bg-white">
                <div class="mx-auto max-w-7xl px-6 py-8 md:px-12">
                    <div class="border-t border-gray-200 pt-6">
                        <form action="{{ route('products.index') }}" method="GET" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <input type="hidden" name="category" value="{{ $selectedCategory }}">

                            <div class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                                <label class="relative block flex-1">
                                    <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                            <path d="M14.1667 14.1667L17.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            <circle cx="8.75" cy="8.75" r="5.83333" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                    </span>
                                    <input
                                        type="search"
                                        name="search"
                                        value="{{ $search }}"
                                        placeholder="Search products, materials, or styles"
                                        class="h-12 w-full border border-gray-300 bg-white pl-11 pr-4 text-sm text-black outline-none transition focus:border-black"
                                    >
                                </label>

                                <div class="flex items-center gap-3">
                                    <details class="group relative">
                                        <summary class="flex h-12 cursor-pointer list-none items-center gap-2 border border-gray-300 bg-white px-5 text-[11px] font-bold uppercase tracking-[0.22em] text-black transition hover:border-black">
                                            <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                <path d="M3.33337 5H16.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M5.83337 10H14.1667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path d="M8.33337 15H11.6667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                            All Filters
                                        </summary>

                                        <div class="absolute right-0 top-[calc(100%+12px)] z-20 w-[min(92vw,360px)] border border-gray-200 bg-white p-5 shadow-[0_24px_60px_rgba(0,0,0,0.12)]">
                                            <div class="space-y-5">
                                                <div>
                                                    <p class="mb-2 text-[10px] font-black uppercase tracking-[0.22em] text-gray-500">
                                                        Price Range
                                                    </p>
                                                    <div class="grid grid-cols-2 gap-3">
                                                        <input
                                                            type="number"
                                                            name="min_price"
                                                            min="0"
                                                            step="0.01"
                                                            value="{{ $minPrice }}"
                                                            placeholder="Min"
                                                            class="h-11 w-full border border-gray-300 bg-white px-4 text-sm text-black outline-none transition focus:border-black"
                                                        >
                                                        <input
                                                            type="number"
                                                            name="max_price"
                                                            min="0"
                                                            step="0.01"
                                                            value="{{ $maxPrice }}"
                                                            placeholder="Max"
                                                            class="h-11 w-full border border-gray-300 bg-white px-4 text-sm text-black outline-none transition focus:border-black"
                                                        >
                                                    </div>
                                                </div>

                                                <label class="flex items-center justify-between border border-gray-200 px-4 py-3">
                                                    <div>
                                                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-black">
                                                            In Stock
                                                        </p>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            Show only available pieces
                                                        </p>
                                                    </div>
                                                    <input
                                                        type="checkbox"
                                                        name="in_stock"
                                                        value="1"
                                                        @checked($inStock)
                                                        class="h-4 w-4 border-gray-300 text-[#064E3B] focus:ring-[#064E3B]"
                                                    >
                                                </label>
                                            </div>

                                            <div class="mt-5 flex items-center justify-between gap-3">
                                                <a
                                                    href="{{ route('products.index', array_filter(['category' => $selectedCategory !== '' ? $selectedCategory : null], static fn ($value) => $value !== null && $value !== '')) }}"
                                                    class="text-[10px] font-black uppercase tracking-[0.22em] text-gray-500 transition hover:text-black"
                                                >
                                                    Reset
                                                </a>
                                                <button
                                                    type="submit"
                                                    class="h-11 border border-black bg-black px-5 text-[11px] font-bold uppercase tracking-[0.2em] text-white transition hover:bg-white hover:text-black"
                                                >
                                                    Apply Filters
                                                </button>
                                            </div>
                                        </div>
                                    </details>

                                    <select
                                        name="sort"
                                        class="h-12 border border-gray-300 bg-white px-4 text-sm text-black outline-none transition focus:border-black"
                                    >
                                        <option value="newest" @selected($sort === 'newest')>Newest</option>
                                        <option value="price_low" @selected($sort === 'price_low')>Price: Low to High</option>
                                        <option value="price_high" @selected($sort === 'price_high')>Price: High to Low</option>
                                        <option value="name" @selected($sort === 'name')>Name</option>
                                    </select>

                                    <button
                                        type="submit"
                                        class="h-12 border border-black bg-black px-6 text-[11px] font-bold uppercase tracking-[0.22em] text-white transition hover:bg-white hover:text-black"
                                    >
                                        Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="mt-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div class="text-[10px] font-black uppercase tracking-[0.25em] text-gray-500">
                            Showing {{ $products->count() }} of {{ $products->total() }}
                        </div>
                    </div>

                    @if ($products->isEmpty())
                        <div class="border border-black bg-[#FAFAFA] px-8 py-20 text-center">
                            <h2 class="text-4xl md:text-5xl font-light uppercase tracking-[0.16em] text-black">
                                No <span class="font-bold">Products</span>
                            </h2>
                            <div class="w-16 h-1 bg-[#064E3B] mx-auto mt-6 mb-6"></div>
                            <p class="mx-auto max-w-xl text-[11px] md:text-sm uppercase tracking-[0.22em] leading-loose text-gray-500 font-medium">
                                Try a broader search or remove one of the filters to reveal more handcrafted pieces.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                            @foreach ($products as $product)
                                @php
                                    $image = $product->images[0] ?? null;
                                    $imageUrl = $image
                                        ? (str_starts_with($image, 'http') ? $image : Storage::url($image))
                                        : null;
                                    $rating = round((float) ($product->reviews_avg_rating ?? 0), 1);
                                    $fullStars = max(0, min(5, (int) round($rating)));
                                    $productInitial = strtoupper(mb_substr($product->name ?? 'P', 0, 1));
                                    $shippingLabel = $product->price >= 1200 ? 'Free shipping' : null;
                                    $stockMessage = match (true) {
                                        $product->stock <= 0 => 'Currently unavailable',
                                        $product->stock === 1 => 'Only 1 left - order soon',
                                        $product->stock <= 3 => 'Only ' . $product->stock . ' left - order soon',
                                        default => null,
                                    };
                                @endphp

                                <article class="group">
                                    <a href="{{ route('products.show', $product) }}" class="block">
                                    <div class="relative aspect-[4/3] overflow-hidden bg-[#F1F1F1] border border-gray-100">
                                            @if ($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                                            @else
                                                <div class="absolute inset-0 bg-[linear-gradient(135deg,#f8faf8_0%,#eef6f1_48%,#f3f4f6_100%)]"></div>
                                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                                    <div class="flex h-24 w-24 items-center justify-center border border-[#064E3B]/20 bg-white text-4xl font-light uppercase text-[#064E3B] shadow-sm">
                                                        {{ $productInitial }}
                                                    </div>
                                                    <p class="mt-5 text-[10px] font-black uppercase tracking-[0.3em] text-gray-400">
                                                        {{ $product->category?->name ?? 'Handcrafted Piece' }}
                                                    </p>
                                                </div>
                                            @endif
                                    </div>

                                    <div class="mt-3 space-y-1.5 text-left">
                                        <h2 class="line-clamp-2 text-[15px] font-medium leading-snug text-black">
                                            {{ \Illuminate\Support\Str::limit($product->name, 58) }}
                                        </h2>

                                        <p class="text-[17px] font-bold leading-none text-black">
                                            {{ number_format((float) $product->price, 2) }} MAD
                                        </p>

                                        <div class="flex items-center gap-1 pt-0.5">
                                            @for ($star = 1; $star <= 5; $star++)
                                                <span class="text-[13px] leading-none {{ $star <= $fullStars ? 'text-black' : 'text-gray-200' }}">&#9733;</span>
                                            @endfor
                                            <span class="ml-1 text-[13px] text-gray-500">
                                                @if ($product->reviews_count > 0)
                                                    ({{ $product->reviews_count }})
                                                @else
                                                    New
                                                @endif
                                            </span>
                                        </div>

                                        <p class="pt-0.5 text-[14px] text-gray-600">
                                            By {{ $product->store?->name ?? 'Lamsa Seller' }}
                                        </p>

                                        <div class="flex flex-wrap items-center gap-2 pt-1">
                                            @if ($shippingLabel)
                                                <span class="inline-flex items-center bg-[#B7D96C] px-2.5 py-1 text-[11px] font-bold leading-none text-[#1f3a08]">
                                                    {{ $shippingLabel }}
                                                </span>
                                            @endif

                                            <span class="inline-flex items-center border border-gray-200 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.16em] text-gray-500">
                                                {{ $product->category?->name ?? 'Uncategorized' }}
                                            </span>
                                        </div>

                                        @if ($stockMessage)
                                            <p class="pt-1 text-[13px] font-medium text-[#B42318]">
                                                {{ $stockMessage }}
                                            </p>
                                        @endif
                                    </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>

                        <div class="pt-10">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </section>
        </main>

        <x-footer />
    </body>
</html>
