<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $auction->name }} | {{ config('app.name', 'Lamsa') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
        </style>
    </head>
    <body class="bg-[#FAFAFA] font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white overflow-x-hidden">
        <x-navbar />

        @php
            $auctionImages = collect($auction->images ?? [])
                ->filter()
                ->map(fn ($image) => str_starts_with($image, 'http') ? $image : Storage::url($image))
                ->values();
            $heroImage = $auctionImages->first();
            $highestBidAmount = $auction->bids->max('amount') ?? $auction->current_price ?? $auction->starting_price;
            $minimumNextBid = $auction->minimumNextBid();
            $bidRestrictionMessage = null;

            if (! auth()->check()) {
                $bidRestrictionMessage = 'Log in with a buyer account to place a bid.';
            } elseif (! auth()->user()->hasRole('buyer')) {
                $bidRestrictionMessage = 'Only buyer accounts can place bids on auctions.';
            } elseif (auth()->user()->artisan && auth()->user()->artisan->id === $auction->artisan_id) {
                $bidRestrictionMessage = 'You cannot bid on your own auction.';
            } elseif (! $auction->canAcceptBids()) {
                $bidRestrictionMessage = 'This auction is not currently accepting bids.';
            }
        @endphp

        <main class="pt-[92px]">
            <section class="bg-white">
                <div class="mx-auto max-w-7xl px-6 py-8 md:px-12">
                    <div class="mb-5 flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">
                        <a href="{{ route('auctions.index') }}" class="transition hover:text-[#222222]">Auctions</a>
                        <span>/</span>
                        <a href="{{ route('auctions.index', ['category' => $auction->category?->slug]) }}" class="transition hover:text-[#222222]">
                            {{ $auction->category?->name ?? 'Details' }}
                        </a>
                    </div>

                    <div class="grid gap-8 xl:grid-cols-[0.9fr_0.7fr] xl:gap-10">
                        <div class="border border-[#ece4d8] bg-[#f6f2eb]">
                            <div class="aspect-[4/3] overflow-hidden">
                                @if ($heroImage)
                                    <img src="{{ $heroImage }}" alt="{{ $auction->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#fbfaf7_0%,#f2eee5_100%)]">
                                        <div class="flex h-28 w-28 items-center justify-center border border-[#ddd4c8] bg-white text-5xl font-light text-[#8b5a33]">
                                            {{ strtoupper(mb_substr($auction->name, 0, 1)) }}
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <aside class="space-y-6 bg-white p-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-black px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.22em] text-white">
                                    Live Auction
                                </span>
                                <span class="text-sm text-gray-500">
                                    Ends {{ $auction->ends_at?->diffForHumans() }}
                                </span>
                            </div>

                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-gray-500">{{ $auction->store?->name ?? 'Lamsa Auction' }}</p>
                                <h1 class="mt-2 text-[2rem] font-medium leading-tight text-[#222222]">
                                    {{ $auction->name }}
                                </h1>
                            </div>

                            <div class="space-y-1">
                                <p class="text-sm font-medium uppercase tracking-[0.2em] text-gray-400">Current bid</p>
                                <p class="text-[3rem] font-semibold leading-none text-[#111111]">
                                    {{ number_format((float) $highestBidAmount, 2) }} MAD
                                </p>
                                <p class="text-sm text-gray-500">{{ $auction->bids->count() }} {{ \Illuminate\Support\Str::plural('bid', $auction->bids->count()) }} placed</p>
                            </div>

                            <div class="mt-8 space-y-4 border border-[#eee5db] bg-[#fdfdfd] p-8">
                                <p class="mb-2 text-[10px] font-black uppercase tracking-[0.22em] text-[#064E3B]">Acquisition Protocol</p>

                                @if (session('success'))
                                    <div class="border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                                        {{ session('success') }}
                                    </div>
                                @endif

                                @if (session('error'))
                                    <div class="border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                                        {{ session('error') }}
                                    </div>
                                @endif

                                @if ($bidRestrictionMessage)
                                    <div class="space-y-4">
                                        <div class="border border-[#eee5db] bg-white px-5 py-4 text-sm text-gray-600">
                                            {{ $bidRestrictionMessage }}
                                        </div>

                                        @guest
                                            <a href="{{ route('login') }}" class="group relative flex w-full items-center justify-center overflow-hidden bg-black py-6 text-[11px] font-black uppercase tracking-[0.4em] text-white transition-all hover:bg-[#064E3B]">
                                                <span class="relative z-10">Log In to Bid</span>
                                            </a>
                                        @else
                                            <a href="{{ route('bids.my') }}" class="group relative flex w-full items-center justify-center overflow-hidden border border-black bg-white py-6 text-[11px] font-black uppercase tracking-[0.4em] text-black transition-all hover:bg-black hover:text-white">
                                                <span class="relative z-10">View My Bids</span>
                                            </a>
                                        @endguest
                                    </div>
                                @else
                                    <form action="{{ route('bids.store', $auction) }}" method="POST" class="space-y-4">
                                        @csrf
                                        <div class="relative">
                                            <input type="number"
                                                   name="amount"
                                                   step="0.01"
                                                   min="{{ $minimumNextBid }}"
                                                   value="{{ old('amount', number_format($minimumNextBid, 2, '.', '')) }}"
                                                   placeholder="Enter bid amount"
                                                   class="w-full border border-gray-200 bg-white px-6 py-4 text-sm font-bold uppercase tracking-[0.2em] transition-colors focus:border-black focus:outline-none">
                                            <span class="absolute right-6 top-1/2 -translate-y-1/2 text-[10px] font-black text-gray-400">MAD</span>
                                        </div>

                                        @error('amount')
                                            <p class="text-sm font-medium text-rose-600">{{ $message }}</p>
                                        @enderror

                                        <button type="submit" class="group relative w-full overflow-hidden bg-black py-6 text-[11px] font-black uppercase tracking-[0.4em] text-white transition-all hover:bg-[#064E3B]">
                                            <span class="relative z-10">Place Your Bid</span>
                                            <div class="absolute inset-0 translate-y-full bg-[#064E3B] transition-transform duration-500 ease-out group-hover:translate-y-0"></div>
                                        </button>

                                        <p class="text-[9px] text-center text-gray-400 uppercase tracking-widest italic">
                                            Minimum next bid: {{ number_format($minimumNextBid, 2) }} MAD • Secure Bidding
                                        </p>
                                    </form>
                                @endif
                            </div>

                            <div class="border-t border-[#eee5db] pt-5 text-[15px] leading-7 text-[#4b4b4b]">
                                {{ $auction->description ?: 'This auction piece is presented directly by the artisan for a limited-time bidding window.' }}
                            </div>

                            <div class="grid gap-4 border-t border-[#eee5db] pt-5 sm:grid-cols-2">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-gray-400">Category</p>
                                    <p class="mt-2 text-lg font-medium text-[#222222]">{{ $auction->category?->name ?? 'Auction piece' }}</p>
                                </div>
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.22em] text-gray-400">Starting price</p>
                                    <p class="mt-2 text-lg font-medium text-[#222222]">{{ number_format((float) $auction->starting_price, 2) }} MAD</p>
                                </div>
                            </div>
                        </aside>
                    </div>

                    <div class="mt-10 border-t border-[#ece4d8] pt-8">
                        <div class="flex items-center justify-between gap-4">
                            <h2 class="text-2xl font-medium text-[#222222]">Recent bids</h2>
                            <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-gray-400">{{ $auction->bids->count() }} total bids</p>
                        </div>

                        @if ($auction->bids->isEmpty())
                            <div class="mt-6 border border-gray-200 bg-[#FAFAFA] px-8 py-16 text-center">
                                <p class="text-lg font-medium text-[#222222]">No bids yet</p>
                                <p class="mt-3 text-sm text-gray-500">This auction is live, but nobody has placed a bid yet.</p>
                            </div>
                        @else
                            <div class="mt-6 space-y-4">
                                @foreach ($auction->bids->sortByDesc('created_at')->take(8) as $bid)
                                    <div class="flex items-center justify-between border border-gray-200 bg-white px-5 py-4">
                                        <div>
                                            <p class="text-base font-medium text-[#222222]">{{ $bid->user?->name ?? 'Bidder' }}</p>
                                            <p class="mt-1 text-sm text-gray-500">{{ $bid->created_at?->diffForHumans() }}</p>
                                        </div>
                                        <p class="text-xl font-semibold text-[#111111]">{{ number_format((float) $bid->amount, 2) }} MAD</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <x-footer />
        </main>
    </body>
</html>
