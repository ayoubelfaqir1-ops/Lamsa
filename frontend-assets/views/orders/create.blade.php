<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Checkout | {{ config('app.name', 'Lamsa') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
        </style>
    </head>
    <body class="min-h-screen bg-[#fcfaf7] font-sans antialiased text-[#222222] selection:bg-[#111111] selection:text-white">
        <x-navbar />

        <main class="px-6 pb-24 pt-24 md:px-12 lg:px-20">
            <div class="mx-auto max-w-7xl">
                <div class="mb-10 flex items-center justify-between gap-6 border-b border-[#ece4d8] pb-6">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">Secure checkout</p>
                        <h1 class="mt-2 text-4xl font-medium text-[#222222]">Review your order</h1>
                    </div>
                    <a href="{{ route('cart.index') }}" class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500 transition hover:text-[#111111]">
                        Back to cart
                    </a>
                </div>

                <div class="grid gap-10 xl:grid-cols-[1.1fr_0.7fr]">
                    <form action="{{ route('orders.store') }}" method="POST" class="space-y-8">
                        @csrf

                        <section class="border border-[#ece4d8] bg-white p-6 md:p-8">
                            <h2 class="text-xl font-medium text-[#222222]">Shipping details</h2>
                            <div class="mt-6 space-y-5">
                                <div>
                                    <label for="shipping_address" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500">Shipping address</label>
                                    <textarea
                                        id="shipping_address"
                                        name="shipping_address"
                                        rows="4"
                                        class="w-full border border-[#ddd4c8] bg-white px-4 py-3 text-sm text-[#222222] focus:border-[#111111] focus:outline-none"
                                        placeholder="Street, city, region, and any delivery notes"
                                        required
                                    >{{ old('shipping_address') }}</textarea>
                                    @error('shipping_address')
                                        <p class="mt-2 text-sm text-[#b14d16]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="payment_method" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500">Payment method</label>
                                    <select
                                        id="payment_method"
                                        name="payment_method"
                                        class="w-full border border-[#ddd4c8] bg-white px-4 py-3 text-sm text-[#222222] focus:border-[#111111] focus:outline-none"
                                        required
                                    >
                                        <option value="">Select a payment method</option>
                                        <option value="cash" @selected(old('payment_method') === 'cash')>Cash on delivery</option>
                                        <option value="card" @selected(old('payment_method') === 'card')>Card</option>
                                    </select>
                                    <p class="mt-2 text-sm text-gray-500">Cash on delivery confirms the order immediately. Card keeps the order pending until the card payment step is confirmed.</p>
                                    @error('payment_method')
                                        <p class="mt-2 text-sm text-[#b14d16]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="notes" class="mb-2 block text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500">Notes for the artisan</label>
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="3"
                                        class="w-full border border-[#ddd4c8] bg-white px-4 py-3 text-sm text-[#222222] focus:border-[#111111] focus:outline-none"
                                        placeholder="Optional delivery or handling notes"
                                    >{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <p class="mt-2 text-sm text-[#b14d16]">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="border border-[#ece4d8] bg-white p-6 md:p-8">
                            <div class="flex items-center justify-between gap-4">
                                <h2 class="text-xl font-medium text-[#222222]">Items in this checkout</h2>
                                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">{{ $cartItems->count() }} pieces</p>
                            </div>

                            <div class="mt-6 space-y-5">
                                @foreach ($cartItems as $index => $item)
                                    @php
                                        $itemImage = collect($item->images ?? [])
                                            ->filter()
                                            ->map(fn ($image) => str_starts_with($image, 'http') ? $image : Storage::url($image))
                                            ->first();
                                    @endphp

                                    <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}">

                                    <article class="flex items-center gap-4 border-t border-[#f1ebe2] pt-5 first:border-t-0 first:pt-0">
                                        <div class="h-20 w-20 shrink-0 overflow-hidden border border-[#ece4d8] bg-[#f6f2eb]">
                                            @if ($itemImage)
                                                <img src="{{ $itemImage }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#fbfaf7_0%,#f2eee5_100%)] text-3xl font-light text-[#8b5a33]">
                                                    {{ strtoupper(mb_substr($item->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">{{ $item->store?->name ?? 'Lamsa Artisan' }}</p>
                                            <h3 class="mt-1 text-lg font-medium text-[#222222]">{{ $item->name }}</h3>
                                            <p class="mt-2 text-sm text-gray-500">Quantity: {{ $item->quantity }}</p>
                                        </div>
                                        <p class="text-lg font-semibold text-[#111111]">{{ number_format((float) $item->price * $item->quantity, 2) }} MAD</p>
                                    </article>
                                @endforeach
                            </div>

                            @error('items')
                                <p class="mt-4 text-sm text-[#b14d16]">{{ $message }}</p>
                            @enderror
                        </section>

                        <button type="submit" class="flex h-14 w-full items-center justify-center bg-[#111111] text-sm font-semibold uppercase tracking-[0.2em] text-white transition hover:bg-[#2a2a2a]">
                            Place order
                        </button>
                    </form>

                    <aside class="self-start border border-[#ece4d8] bg-white p-6 md:p-8 xl:sticky xl:top-[88px]">
                        <h2 class="text-xl font-medium text-[#222222]">Order summary</h2>
                        <div class="mt-6 space-y-4 text-sm text-gray-600">
                            <div class="flex items-center justify-between">
                                <span>Items subtotal</span>
                                <span class="font-medium text-[#222222]">{{ number_format((float) $total, 2) }} MAD</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span>Delivery</span>
                                <span class="font-medium text-[#222222]">Free</span>
                            </div>
                            <div class="flex items-center justify-between border-b border-[#ece4d8] pb-4">
                                <span>Tax</span>
                                <span class="font-medium text-[#222222]">0.00 MAD</span>
                            </div>
                            <div class="flex items-end justify-between pt-2">
                                <span class="text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500">Total</span>
                                <span class="text-3xl font-semibold text-[#111111]">{{ number_format((float) $total, 2) }} MAD</span>
                            </div>
                        </div>

                        <div class="mt-8 border-t border-[#ece4d8] pt-6 text-sm leading-7 text-gray-600">
                            Your order may be split into separate artisan orders automatically when your cart contains pieces from different makers.
                        </div>
                    </aside>
                </div>
            </div>
        </main>

        <x-footer />
    </body>
</html>
