<div class="space-y-5">
    @if ($message)
        <div class="border px-4 py-3 text-sm {{ $messageType === 'success' ? 'border-[#d6eadf] bg-[#f2faf6] text-[#064E3B]' : 'border-[#f1d4c9] bg-[#fff7f3] text-[#b14d16]' }}">
            {{ $message }}
        </div>
    @endif

    <div>
        <p class="mb-3 text-sm font-semibold text-[#222222]">Quantity</p>
        <div class="space-y-3">
            <div class="inline-flex h-12 items-center border border-[#d8cec1] bg-white px-3">
                <button
                    type="button"
                    wire:click="decrementQuantity"
                    class="flex h-full w-12 items-center justify-center text-[1.55rem] font-light leading-none text-[#222222] transition hover:bg-[#f7f3ed] disabled:cursor-not-allowed disabled:text-gray-300"
                    @disabled(! $this->hasStock || $quantity <= 1)
                >
                    -
                </button>
                <input
                    type="number"
                    min="{{ $this->hasStock ? 1 : 0 }}"
                    max="{{ $product->stock }}"
                    wire:model.live="quantity"
                    class="h-full w-12 border-0 bg-transparent p-0 text-center text-[1.1rem] font-medium text-[#222222] focus:outline-none focus:ring-0"
                    @disabled(! $this->hasStock)
                >
                <button
                    type="button"
                    wire:click="incrementQuantity"
                    class="flex h-full w-12 items-center justify-center text-[1.55rem] font-light leading-none text-[#222222] transition hover:bg-[#f7f3ed] disabled:cursor-not-allowed disabled:text-gray-300"
                    @disabled(! $this->hasStock || $quantity >= $product->stock)
                >
                    +
                </button>
            </div>
            <p class="text-sm text-gray-500">{{ $this->availabilityNote }}</p>
        </div>
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button
            type="button"
            wire:click="addToCart"
            wire:loading.attr="disabled"
            class="flex h-14 flex-1 items-center justify-center bg-[#111111] text-base font-semibold text-white transition hover:bg-[#2a2a2a] disabled:cursor-not-allowed disabled:bg-[#b8b1a6]"
            @disabled(! $this->hasStock)
        >
            <span wire:loading.remove wire:target="addToCart">
                {{ $this->hasStock ? 'Add to cart' : 'Request this piece' }}
            </span>
            <span wire:loading wire:target="addToCart">Adding...</span>
        </button>

        <button type="button" class="flex h-14 w-14 items-center justify-center border border-[#d8cec1] bg-white text-[#222222] transition hover:border-[#111111]">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
        </button>
    </div>
</div>
