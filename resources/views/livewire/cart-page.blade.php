<div>
    @if ($message)
        <div class="mb-6 border px-4 py-3 text-sm {{ $messageType === 'success' ? 'border-[#d6eadf] bg-[#f2faf6] text-[#064E3B]' : 'border-[#f1d4c9] bg-[#fff7f3] text-[#b14d16]' }}">
            {{ $message }}
        </div>
    @endif

    <a href="{{ route('products.index') }}" class="mb-8 inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-widest text-gray-400 transition-colors hover:text-black">
        <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
        Back to Gallery
    </a>

    <div class="mb-16">
        <h1 class="text-4xl font-light uppercase tracking-[0.2em] text-black">Your Cart</h1>
    </div>

    @if($cart->isEmpty())
        <div class="flex flex-col items-center justify-center border-t border-gray-100 py-32 text-center">
            <p class="mb-12 text-[10px] font-bold uppercase tracking-[0.5em] text-gray-300">The collection is currently void</p>
            <a href="{{ route('products.index') }}" class="border border-black px-10 py-4 text-[10px] font-black uppercase tracking-[0.3em] transition-all hover:bg-black hover:text-white">
                Explore Archive
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 gap-16 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <div class="border-t border-gray-100">
                    @foreach($cart as $item)
                        <div class="group flex items-center gap-6 border-b border-gray-100 py-10">
                            <button type="button" wire:click="remove({{ $item->id }})" class="shrink-0 text-gray-300 transition-colors hover:text-black">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>

                            <div class="relative h-24 w-24 shrink-0 overflow-hidden border border-gray-100 bg-[#F8F8F8]">
                                @php
                                    $itemImage = collect($item->images ?? [])
                                        ->filter()
                                        ->map(fn ($image) => str_starts_with($image, 'http') ? $image : Storage::url($image))
                                        ->first();
                                @endphp
                                <img src="{{ $itemImage ?: 'https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&q=80' }}"
                                     alt="{{ $item->name }}"
                                     class="h-full w-full object-cover grayscale transition-all duration-700 group-hover:grayscale-0">
                            </div>

                            <div class="min-w-0 flex-grow">
                                <h3 class="truncate text-sm font-bold uppercase tracking-widest text-black">{{ $item->name }}</h3>
                                <p class="mt-1 text-[10px] font-medium uppercase tracking-widest text-gray-400">{{ $item->category->name }} / {{ $item->store->name }}</p>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center border border-gray-100">
                                    <button
                                        type="button"
                                        wire:click="decrement({{ $item->id }})"
                                        class="flex h-8 w-8 items-center justify-center text-gray-400 transition-all hover:bg-gray-50 hover:text-black"
                                        @disabled($item->quantity <= 1)
                                    >
                                        <svg class="h-2 w-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M20 12H4"></path></svg>
                                    </button>
                                    <span class="flex h-8 w-8 items-center justify-center border-x border-gray-100 text-[10px] font-bold">{{ $item->quantity }}</span>
                                    <button
                                        type="button"
                                        wire:click="increment({{ $item->id }})"
                                        class="flex h-8 w-8 items-center justify-center text-gray-400 transition-all hover:bg-gray-50 hover:text-black"
                                    >
                                        <svg class="h-2 w-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </div>

                            <div class="min-w-[100px] shrink-0 text-right">
                                <p class="text-sm font-black text-black">{{ number_format($item->price * $item->quantity, 2) }} MAD</p>
                                <p class="text-[9px] font-bold uppercase tracking-widest text-gray-400">{{ number_format($item->price, 2) }} MAD / unit</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-12 flex flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
                    <button type="button" wire:click="clear" class="flex items-center gap-2 text-[10px] font-black uppercase tracking-widest text-gray-400 transition-colors hover:text-black">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Clear Cart
                    </button>
                    <a href="{{ route('products.index') }}" class="text-[10px] font-black uppercase tracking-widest text-gray-400 transition-colors hover:text-black">
                        Continue Shopping
                    </a>
                </div>
            </div>

            <div class="border-l border-gray-100 lg:col-span-4 lg:pl-16">
                <div class="sticky top-40 space-y-12">
                    <h2 class="text-xl font-light uppercase tracking-[0.2em] text-black">Cart Totals</h2>

                    <div class="space-y-6">
                        <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest text-gray-400">
                            <span>Shipping</span>
                            <span class="text-black">Free</span>
                        </div>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-6 text-[10px] font-bold uppercase tracking-widest text-gray-400">
                            <span>Tax</span>
                            <span class="text-black">0.00 MAD</span>
                        </div>
                        <div class="flex items-center justify-between pt-2">
                            <span class="text-[11px] font-black uppercase tracking-luxury text-black">Total</span>
                            <span class="text-2xl font-black tracking-tighter">{{ number_format($total, 2) }} MAD</span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        @auth
                            <a href="{{ route('orders.create') }}" class="group relative flex w-full items-center justify-center overflow-hidden bg-black py-6 text-[10px] font-black uppercase tracking-[0.4em] text-white">
                                <span class="relative z-10">Proceed to Checkout</span>
                                <div class="absolute inset-0 translate-y-full bg-[#064E3B] transition-transform duration-500 ease-out group-hover:translate-y-0"></div>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="group relative flex w-full items-center justify-center overflow-hidden bg-black py-6 text-[10px] font-black uppercase tracking-[0.4em] text-white">
                                <span class="relative z-10">Log In to Checkout</span>
                                <div class="absolute inset-0 translate-y-full bg-[#064E3B] transition-transform duration-500 ease-out group-hover:translate-y-0"></div>
                            </a>
                            <p class="text-center text-[10px] uppercase tracking-[0.18em] text-gray-400">
                                Create or log into your account to place the order.
                            </p>
                        @endauth

                        <a href="{{ route('products.index') }}" class="block text-center text-[9px] font-black uppercase tracking-[0.3em] text-gray-400 transition-colors hover:text-black italic">
                            Continue Shopping
                        </a>
                    </div>

                    <div class="flex items-center justify-center gap-6 border-t border-gray-50 pt-12 opacity-30">
                        <div class="h-px w-8 bg-black"></div>
                        <span class="text-[8px] font-black uppercase tracking-luxury">Lamsa Protocol</span>
                        <div class="h-px w-8 bg-black"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
