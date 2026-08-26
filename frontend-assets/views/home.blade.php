<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Lamsa - Artisanal Heritage') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
        </style>
    </head>
    <body class="overflow-x-hidden bg-[#FAFAFA] font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white">
        @php
            $brandVideoUrl = \Illuminate\Support\Facades\Storage::url('site/videos/lamsa-hero-section-video.mp4');
            $manifestoImageUrl = \Illuminate\Support\Facades\Storage::url('site/images/manifesto_weaving_zarbiya.png');
        @endphp

        <x-navbar />

        <header class="relative flex h-screen w-full flex-col items-center justify-center overflow-hidden text-center">
            <video autoplay loop muted playsinline class="absolute inset-0 z-0 h-full w-full object-cover">
                <source src="{{ $brandVideoUrl }}" type="video/mp4">
            </video>
            <div class="absolute inset-0 z-10 bg-black/30"></div>
            <div class="absolute inset-0 z-10 bg-gradient-to-t from-black via-transparent to-black/60"></div>

            <div class="relative z-20 mx-auto flex max-w-5xl flex-col items-center px-6">
                <h1 class="text-5xl font-light uppercase leading-none tracking-widest text-white md:text-7xl lg:text-[10rem]">Lamsa</h1>
                <div class="my-6 h-1 w-16 bg-white md:my-10 md:w-32"></div>
                <p class="max-w-2xl text-[10px] font-medium uppercase leading-[2] tracking-[0.4em] text-gray-300 drop-shadow-2xl md:text-xs">
                    Where ancestral mastery meets the digital age. Discover a curated collective of Moroccan craft, handpicked to bring the soul of the Medina into your modern world.
                </p>
                <div class="mt-16 flex flex-col items-center gap-8 sm:flex-row">
                    <a href="#collections" class="group relative overflow-hidden bg-white px-12 py-5 text-[11px] font-black uppercase tracking-[0.3em] text-black transition-all hover:bg-[#064E3B] hover:text-white">
                        <span class="relative z-10">Explore Heritage</span>
                        <div class="absolute inset-0 -translate-y-full bg-[#064E3B] transition-transform duration-500 group-hover:translate-y-0"></div>
                    </a>

                    <a href="{{ route('artisan-register') }}" class="group flex items-center gap-4 text-[11px] font-bold uppercase tracking-[0.3em] text-white transition-colors hover:text-emerald-400">
                        <span class="border-b border-white/20 pb-1 group-hover:border-emerald-400">Join the Collective</span>
                        <svg class="h-4 w-4 transition-transform group-hover:translate-x-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </header>

        @php
            $productImageUrl = function ($product) {
                $image = $product->images[0] ?? null;

                return $image
                    ? (str_starts_with($image, 'http') ? $image : \Illuminate\Support\Facades\Storage::url($image))
                    : 'https://images.unsplash.com/photo-1517705008128-361805f42e86?auto=format&fit=crop&q=80';
            };

            $auctionImageUrl = function ($auction) {
                $image = $auction->images[0] ?? null;

                return $image
                    ? (str_starts_with($image, 'http') ? $image : \Illuminate\Support\Facades\Storage::url($image))
                    : 'https://images.unsplash.com/photo-1540306161947-f5e27de22ba6?auto=format&fit=crop&q=80';
            };
        @endphp

        <section id="collections" class="bg-white px-6 py-12 md:px-12 md:py-20" x-data="{ selectedCategory: new URLSearchParams(window.location.search).get('category') || '{{ $defaultCategory }}' }">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 flex flex-col items-center justify-between gap-10 md:flex-row md:items-end">
                    <div class="text-center md:text-left">
                        <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.4em] text-[#064E3B]">Master Artisans</h2>
                        <h2 class="text-2xl font-light uppercase tracking-widest text-black md:text-4xl">The <span class="font-semibold">Collections</span></h2>
                        <div class="mt-4 h-1 w-10 bg-[#064E3B]"></div>
                    </div>

                    <div class="flex flex-wrap gap-6 border-b border-gray-100 md:gap-12">
                        @forelse ($productCollections as $collection)
                            <button
                                @click="selectedCategory = '{{ $collection['slug'] }}'"
                                :class="selectedCategory === '{{ $collection['slug'] }}' ? 'text-black font-bold border-black' : 'text-gray-400 font-medium border-transparent'"
                                class="border-b-2 pb-4 text-[10px] uppercase tracking-widest transition-all"
                            >
                                {{ $collection['name'] }}
                            </button>
                        @empty
                            <span class="pb-4 text-[10px] font-bold uppercase tracking-widest text-gray-400">Collections coming soon</span>
                        @endforelse
                    </div>
                </div>

                <div class="relative overflow-hidden">
                    @forelse ($productCollections as $collection)
                        <div x-show="selectedCategory === '{{ $collection['slug'] }}'" x-cloak class="flex gap-8 overflow-x-auto pb-10 snap-x no-wrap custom-scrollbar">
                            @foreach ($collection['products'] as $product)
                                @php
                                    $rating = round((float) ($product->reviews_avg_rating ?? $product->average_rating ?? 0), 1);
                                @endphp
                                <a href="{{ route('products.show', $product) }}" class="group block w-[280px] shrink-0 snap-start md:w-[320px]">
                                    <div class="aspect-[4/3] overflow-hidden border border-gray-100 bg-[#F1F1F1]">
                                        <img src="{{ $productImageUrl($product) }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" alt="{{ $product->name }}">
                                    </div>
                                    <div class="mt-4 space-y-1.5 text-left">
                                        <h3 class="line-clamp-1 text-[15px] font-medium uppercase leading-snug tracking-tight text-black">{{ $product->name }}</h3>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-[11px] font-bold tracking-tight text-[#064E3B]">{{ number_format($rating, 1) }}</span>
                                            <span class="text-[11px] font-bold tracking-tight text-gray-400">({{ $product->reviews_count }} reviews)</span>
                                        </div>
                                        <p class="text-[17px] font-bold leading-none tracking-widest text-black">{{ number_format((float) $product->price, 2) }} MAD</p>
                                        <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">{{ $product->category?->name ?? 'Collection' }}</p>
                                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#166534]">{{ $product->store?->name ?? 'Lamsa' }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @empty
                        <div class="border border-gray-200 bg-[#FAFAFA] px-8 py-16 text-center">
                            <p class="text-lg font-medium text-[#222222]">No featured products yet</p>
                            <p class="mt-3 text-sm text-gray-500">Publish a few products and they will appear here automatically.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="border-t border-gray-100 bg-[#FAFAFA] px-6 py-12 md:px-12 md:py-20">
            <div class="mx-auto max-w-7xl">
                <div class="mb-16 flex items-end justify-between">
                    <div>
                        <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.4em] text-[#064E3B]">Limited Drops</h2>
                        <h2 class="text-2xl font-light uppercase tracking-widest text-black md:text-4xl">Top <span class="font-semibold">Bids</span></h2>
                        <div class="mt-4 h-1 w-10 bg-[#064E3B]"></div>
                    </div>
                    <a href="{{ route('auctions.index') }}" class="border-b border-black pb-1 text-[10px] font-semibold uppercase tracking-widest text-black transition-all hover:border-gray-400 hover:text-gray-400">Go to Auctions</a>
                </div>

                <div class="flex gap-8 overflow-x-auto pb-10 snap-x no-wrap custom-scrollbar">
                    @forelse ($featuredAuctions as $auction)
                        @php
                            $currentAmount = $auction->highestBid?->amount ?? $auction->current_price ?? $auction->starting_price;
                            $timeLeft = $auction->ends_at ? now()->diffForHumans($auction->ends_at, ['parts' => 2, 'short' => true, 'syntax' => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW]) : null;
                        @endphp
                        <a href="{{ route('auctions.show', $auction) }}" class="group block w-[280px] shrink-0 snap-start md:w-[320px]">
                            <div class="relative aspect-[4/3] overflow-hidden border border-gray-100 bg-[#F1F1F1]">
                                <img src="{{ $auctionImageUrl($auction) }}" class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" alt="{{ $auction->name }}">
                                <div class="absolute left-4 top-4 flex items-center gap-2 bg-[#E8F5E9] px-2.5 py-1 text-[9px] font-black uppercase tracking-[0.18em] text-[#166534] shadow-sm">
                                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-[#166534]"></span> Live
                                </div>
                                @if ($timeLeft)
                                    <div class="absolute right-4 top-4 border border-white/70 bg-white/95 px-3 py-1.5 shadow-sm">
                                        <span class="block text-[11px] font-black uppercase tracking-[0.16em] text-black">{{ $timeLeft }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="mt-4 space-y-1.5 text-left">
                                <h2 class="line-clamp-1 text-[15px] font-medium uppercase leading-snug tracking-tight text-black">{{ $auction->name }}</h2>
                                <p class="text-[17px] font-bold leading-none tracking-widest text-black">{{ number_format((float) $currentAmount, 2) }} MAD</p>
                                <div class="flex items-center justify-between border-t border-gray-100 pt-3">
                                    <p class="text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">Current bid</p>
                                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#166534]">{{ $auction->bids_count }} {{ \Illuminate\Support\Str::plural('Bid', $auction->bids_count) }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="w-full border border-gray-200 bg-white px-8 py-16 text-center">
                            <p class="text-lg font-medium text-[#222222]">No live auctions right now</p>
                            <p class="mt-3 text-sm text-gray-500">As soon as an artisan publishes a live auction, it will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden bg-black px-6 py-10 text-white md:px-12 md:py-16">
            <div class="flex flex-col lg:flex-row">
                <div class="relative flex w-full items-center justify-center overflow-hidden bg-[#0a0a0a] p-6 md:p-10 lg:w-1/2 lg:p-12">
                    <div class="group relative aspect-[4/5] w-full max-w-md overflow-hidden border border-white/5 bg-neutral-900 shadow-2xl">
                        <img src="{{ $manifestoImageUrl }}" alt="Zarbiya Weaving" class="absolute inset-0 h-full w-full object-cover grayscale transition-all duration-[2000ms] group-hover:scale-110 group-hover:grayscale-0">
                        <div class="absolute inset-0 bg-black/40 transition-colors duration-1000 group-hover:bg-transparent"></div>
                        <div class="pointer-events-none absolute inset-6 border border-white/10"></div>
                    </div>
                    <div class="pointer-events-none absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 select-none text-[18vw] font-black uppercase tracking-tighter text-white/[0.02]">HANDS</div>
                </div>

                <div class="relative flex w-full flex-col justify-center py-8 lg:w-1/2 lg:px-10">
                    <span class="mb-4 block text-xs font-bold uppercase tracking-[0.4em] text-[#10B981]">The Lamsa Manifesto</span>
                    <h2 class="text-2xl font-light uppercase tracking-widest text-white md:text-4xl">
                        Behind every <br>piece, there <br>are <span class="font-bold italic text-[#F0FDF4]">hands.</span>
                    </h2>
                    <div class="mt-4 h-1 w-10 bg-[#10B981]"></div>
                    <div class="mt-8 max-w-xl space-y-6">
                        <p class="border-l-4 border-[#064E3B] pl-8 text-base font-medium uppercase leading-relaxed tracking-wide md:text-lg">
                            Hands that have shaped clay since childhood, stretched leather under the heat of Fes, placed every tile of zellige with a precision passed down through generations.
                        </p>
                        <p class="text-sm font-medium uppercase italic leading-loose tracking-[0.2em] text-gray-400 md:text-base">
                            Lamsa is not a shop. It is the direct line between you and the artisans the world never gets to see until now.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-[#F0FDF4] border-t border-gray-100 px-6 py-16 md:px-12 md:py-24">
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-16 lg:grid-cols-12 lg:gap-24">
                <div class="h-fit lg:sticky lg:top-32 lg:col-span-4">
                    <h2 class="mb-6 text-xs font-bold uppercase tracking-[0.4em] text-[#064E3B]">The Craft Pipeline</h2>
                    <h3 class="text-4xl font-light uppercase leading-tight tracking-widest text-black md:text-5xl">
                        How we <br><span class="font-bold">bridge</span> the <br>distance.
                    </h3>
                </div>

                <div class="space-y-16 lg:col-span-8 lg:space-y-24">
                    <div class="group flex flex-col items-start gap-12 md:flex-row">
                        <span class="text-7xl font-black leading-none text-black/5 transition-colors duration-700 group-hover:text-[#064E3B]/10 md:text-9xl">01</span>
                        <div class="pt-4 md:pt-12">
                            <h4 class="mb-6 text-xl font-bold uppercase tracking-[0.3em] text-black">Source & Curate</h4>
                            <p class="max-w-md text-sm uppercase leading-relaxed tracking-widest text-gray-500">
                                We go deep into hidden workshops of the Atlas and the Medinas, selecting only those whose mastery is undisputed and heritage is pure.
                            </p>
                        </div>
                    </div>

                    <div class="group flex flex-col items-start gap-12 md:flex-row">
                        <span class="text-7xl font-black leading-none text-black/5 transition-colors duration-700 group-hover:text-[#064E3B]/10 md:text-9xl">02</span>
                        <div class="pt-4 md:pt-12">
                            <h4 class="mb-6 text-xl font-bold uppercase tracking-[0.3em] text-black">Verify & Protect</h4>
                            <p class="max-w-md text-sm uppercase leading-relaxed tracking-widest text-gray-500">
                                Every piece undergoes rigorous authentication. We ensure materials are natural and techniques are traditional.
                            </p>
                        </div>
                    </div>

                    <div class="group flex flex-col items-start gap-12 md:flex-row">
                        <span class="text-7xl font-black leading-none text-black/5 transition-colors duration-700 group-hover:text-[#064E3B]/10 md:text-9xl">03</span>
                        <div class="pt-4 md:pt-12">
                            <h4 class="mb-6 text-xl font-bold uppercase tracking-[0.3em] text-black">Global Bridge</h4>
                            <p class="max-w-md text-sm uppercase leading-relaxed tracking-widest text-gray-500">
                                Your piece is packed with architectural care and shipped directly from the artisan's hands to your world.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <x-footer />
    </body>
</html>
