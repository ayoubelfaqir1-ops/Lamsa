<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Lamsa - Auctions') }}</title>
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

            .auction-countdown[data-expired="true"] {
                color: #b42318;
            }

            .auction-countdown-wrap[data-expired="true"] {
                border-color: #f3d1cc;
                background: #fff4f2;
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
                            Live Auctions
                        </h2>
                        <p class="mx-auto mt-3 max-w-3xl text-sm md:text-base text-gray-500 leading-relaxed">
                            Discover limited Moroccan pieces currently open for bidding, with each listing tied directly to the artisan and workshop behind it.
                        </p>
                    </div>

                    <div class="category-scrollbar mt-6 overflow-x-auto pb-2">
                        <div class="flex min-w-max snap-x gap-3 sm:gap-4 xl:gap-5">
                            @foreach ($categories as $category)
                                <a
                                    href="{{ route('auctions.index', ['category' => $category->slug]) }}"
                                    class="group block w-[220px] shrink-0 snap-start focus:outline-none md:w-[230px] xl:w-[240px]"
                                >
                                    <div class="aspect-[4/5] overflow-hidden border border-gray-100 bg-[#F1F1F1] transition-shadow duration-300 {{ $category->slug === $selectedCategory ? 'ring-2 ring-black ring-offset-2 ring-offset-white' : 'group-hover:shadow-[0_14px_30px_rgba(0,0,0,0.08)]' }}">
                                        @if ($category->image)
                                            <img src="{{ $category->image }}" alt="{{ $category->name }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#f8faf8_0%,#eef6f1_48%,#f3f4f6_100%)]">
                                                <div class="flex h-24 w-24 items-center justify-center border border-[#064E3B]/20 bg-white text-4xl font-light uppercase text-[#064E3B]">
                                                    {{ strtoupper(mb_substr($category->name, 0, 1)) }}
                                                </div>
                                            </div>
                                        @endif
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

            <section class="bg-white">
                <div class="mx-auto max-w-7xl px-6 py-8 md:px-12">
                    <div class="border-t border-gray-200 pt-6">
                        <form action="{{ route('auctions.index') }}" method="GET" class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
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
                                        placeholder="Search auctions, pieces, or artisans"
                                        class="h-12 w-full border border-gray-300 bg-white pl-11 pr-4 text-sm text-black outline-none transition focus:border-black"
                                    >
                                </label>

                                <div class="flex items-center gap-3">
                                    <select
                                        name="sort"
                                        class="h-12 border border-gray-300 bg-white px-4 text-sm text-black outline-none transition focus:border-black"
                                    >
                                        <option value="ending_soon" @selected($sort === 'ending_soon')>Ending Soon</option>
                                        <option value="newest" @selected($sort === 'newest')>Newest</option>
                                        <option value="price_low" @selected($sort === 'price_low')>Current Bid: Low to High</option>
                                        <option value="price_high" @selected($sort === 'price_high')>Current Bid: High to Low</option>
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
                            Showing {{ $auctions->count() }} of {{ $auctions->total() }}
                        </div>
                    </div>

                    @if ($auctions->isEmpty())
                        <div class="border border-black bg-[#FAFAFA] px-8 py-20 text-center">
                            <h2 class="text-4xl md:text-5xl font-light uppercase tracking-[0.16em] text-black">
                                No <span class="font-bold">Auctions</span>
                            </h2>
                            <div class="w-16 h-1 bg-[#064E3B] mx-auto mt-6 mb-6"></div>
                            <p class="mx-auto max-w-xl text-[11px] md:text-sm uppercase tracking-[0.22em] leading-loose text-gray-500 font-medium">
                                There are no live auctions for this filter right now. Try another category or come back soon for new bidding drops.
                            </p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                            @foreach ($auctions as $auction)
                                @php
                                    $image = $auction->images[0] ?? null;
                                    $imageUrl = $image ? (str_starts_with($image, 'http') ? $image : Storage::url($image)) : null;
                                    $highestBidAmount = $auction->highestBid?->amount ?? $auction->current_price ?? $auction->starting_price;
                                @endphp

                                <article class="group">
                                    <a href="{{ route('auctions.show', $auction) }}" class="block">
                                        <div class="relative aspect-[4/3] overflow-hidden bg-[#F1F1F1] border border-gray-100">
                                            @if ($imageUrl)
                                                <img src="{{ $imageUrl }}" alt="{{ $auction->name }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105">
                                            @else
                                                <div class="absolute inset-0 bg-[linear-gradient(135deg,#f8faf8_0%,#eef6f1_48%,#f3f4f6_100%)]"></div>
                                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                                    <div class="flex h-24 w-24 items-center justify-center border border-[#064E3B]/20 bg-white text-4xl font-light uppercase text-[#064E3B]">
                                                        {{ strtoupper(mb_substr($auction->name ?? 'A', 0, 1)) }}
                                                    </div>
                                                    <p class="mt-5 text-[10px] font-black uppercase tracking-[0.3em] text-gray-400">
                                                        {{ $auction->category?->name ?? 'Live Piece' }}
                                                    </p>
                                                </div>
                                            @endif

                                            <div class="absolute left-4 top-4 bg-[#E8F5E9] px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.18em] text-[#166534] shadow-sm">
                                                Live
                                            </div>

                                            @if ($auction->ends_at)
                                                <div class="auction-countdown-wrap absolute right-4 top-4 border border-white/70 bg-white/95 px-3 py-1.5 shadow-sm">
                                                    <span
                                                        class="auction-countdown block text-[11px] font-black uppercase tracking-[0.16em] text-black"
                                                        data-end-time="{{ $auction->ends_at->toIso8601String() }}"
                                                    >
                                                        --h --m --s
                                                    </span>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="mt-3 space-y-1.5 text-left">
                                            <h2 class="line-clamp-2 text-[15px] font-medium leading-snug text-black">
                                                {{ \Illuminate\Support\Str::limit($auction->name, 58) }}
                                            </h2>

                                            <p class="line-clamp-2 text-[13px] leading-6 text-gray-500">
                                                {{ \Illuminate\Support\Str::limit(strip_tags((string) $auction->description), 90) }}
                                            </p>

                                            <p class="text-[17px] font-bold leading-none text-black">
                                                {{ number_format((float) $highestBidAmount, 2) }} MAD
                                            </p>

                                            <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                                                <p class="text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">
                                                    Current bid
                                                </p>
                                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#166534]">
                                                    {{ $auction->bids_count }} {{ \Illuminate\Support\Str::plural('bid', $auction->bids_count) }}
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>

                        <div class="pt-10">
                            {{ $auctions->links() }}
                        </div>
                    @endif
                </div>
            </section>

            <x-footer />
        </main>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const countdowns = document.querySelectorAll('.auction-countdown');

                function formatCountdown(targetDate) {
                    const diff = targetDate.getTime() - Date.now();

                    if (diff <= 0) {
                        return { label: 'Ended', expired: true };
                    }

                    const totalSeconds = Math.floor(diff / 1000);
                    const days = Math.floor(totalSeconds / 86400);
                    const hours = Math.floor((totalSeconds % 86400) / 3600);
                    const minutes = Math.floor((totalSeconds % 3600) / 60);
                    const seconds = totalSeconds % 60;

                    if (days > 0) {
                        return {
                            label: `${String(days).padStart(2, '0')}d ${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m`,
                            expired: false,
                        };
                    }

                    return {
                        label: `${String(hours).padStart(2, '0')}h ${String(minutes).padStart(2, '0')}m ${String(seconds).padStart(2, '0')}s`,
                        expired: false,
                    };
                }

                function syncCountdowns() {
                    countdowns.forEach((countdown) => {
                        const endTime = countdown.dataset.endTime;

                        if (!endTime) {
                            return;
                        }

                        const state = formatCountdown(new Date(endTime));
                        countdown.textContent = state.label;
                        countdown.dataset.expired = state.expired ? 'true' : 'false';
                        countdown.closest('.auction-countdown-wrap')?.setAttribute('data-expired', state.expired ? 'true' : 'false');
                    });
                }

                syncCountdowns();
                if (countdowns.length > 0) {
                    window.setInterval(syncCountdowns, 1000);
                }
            });
        </script>
    </body>
</html>
