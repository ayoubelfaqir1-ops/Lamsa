<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>My Bids | {{ config('app.name', 'Lamsa') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
        </style>
    </head>
    <body class="bg-[#FAFAFA] font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white">
        <x-navbar />

        <main class="pt-[92px]">
            <section class="bg-white">
                <div class="mx-auto max-w-7xl px-6 py-10 md:px-12">
                    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">Buyer dashboard</p>
                            <h1 class="mt-2 text-3xl font-medium text-[#222222]">My Bids</h1>
                            <p class="mt-3 max-w-2xl text-sm text-gray-500">Track the auctions you joined, review your latest offers, and withdraw a bid while the auction is still live.</p>
                        </div>
                        <a href="{{ route('auctions.index') }}" class="inline-flex items-center justify-center border border-black px-5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-black transition hover:bg-black hover:text-white">
                            Explore Auctions
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="space-y-4">
                        @forelse ($bids as $bid)
                            @php
                                $auction = $bid->auction;
                                $canWithdraw = $auction && $auction->canAcceptBids() && $bid->user_id === auth()->id();
                            @endphp
                            <div class="border border-gray-200 bg-[#fcfcfc] p-6">
                                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">{{ $auction?->store?->name ?? 'Auction listing' }}</p>
                                            <h2 class="mt-1 text-2xl font-medium text-[#222222]">{{ $auction?->name ?? 'Auction unavailable' }}</h2>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">
                                            <span>Your bid: {{ number_format((float) $bid->amount, 2) }} MAD</span>
                                            <span>Current: {{ number_format((float) ($auction?->currentBidAmount() ?? $bid->amount), 2) }} MAD</span>
                                            <span>{{ $bid->created_at?->diffForHumans() }}</span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $auction?->category?->name ?? 'Auction piece' }} • Ends {{ $auction?->ends_at?->diffForHumans() ?? 'soon' }}
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-3 lg:min-w-[220px]">
                                        @if ($auction)
                                            <a href="{{ route('auctions.show', $auction) }}" class="inline-flex items-center justify-center border border-black px-4 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-black transition hover:bg-black hover:text-white">
                                                View Auction
                                            </a>
                                        @endif

                                        @if ($canWithdraw)
                                            <form action="{{ route('bids.destroy', $bid) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex w-full items-center justify-center border border-rose-300 px-4 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-rose-700 transition hover:bg-rose-50">
                                                    Withdraw Bid
                                                </button>
                                            </form>
                                        @else
                                            <div class="border border-gray-200 px-4 py-3 text-center text-[10px] font-bold uppercase tracking-[0.18em] text-gray-400">
                                                Withdrawal unavailable
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="border border-gray-200 bg-[#FAFAFA] px-8 py-16 text-center">
                                <p class="text-lg font-medium text-[#222222]">No bids yet</p>
                                <p class="mt-3 text-sm text-gray-500">You have not placed any bids yet. Start with a live auction and make your first offer.</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($bids->hasPages())
                        <div class="mt-8">
                            {{ $bids->links() }}
                        </div>
                    @endif
                </div>
            </section>

            <x-footer />
        </main>
    </body>
</html>
