@php
    $sidebarItems = \App\View\ArtisanSidebar::items('orders', auth()->user()->artisan?->store);
@endphp

<x-dashboard.layout :header-title="'Order #' . $order->id">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <div class="space-y-8 lg:space-y-12">
        <section class="grid gap-4 lg:gap-6 xl:grid-cols-[minmax(0,1.25fr)_20rem]">
            <div class="border border-slate-700 bg-[#111827] p-6 sm:p-8 xl:p-10">
                <div class="space-y-4">
                    <span class="inline-flex w-full items-center justify-center gap-2 border border-emerald-500/20 bg-emerald-500/10 px-4 py-2 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-300 sm:w-auto sm:justify-start">
                        <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                        Order Review
                    </span>
                    <div class="space-y-3">
                        <h1 class="text-2xl font-light uppercase tracking-[0.12em] text-white sm:text-3xl sm:tracking-[0.16em] xl:text-4xl xl:tracking-[0.18em]">
                            Order <span class="font-black italic text-[#10B981]">#{{ $order->id }}</span>
                        </h1>
                        <p class="max-w-3xl text-sm leading-relaxed text-slate-300 sm:text-base">
                            Inspect the buyer, the delivery address, and each item included in this order so you can fulfill it with confidence.
                        </p>
                    </div>
                </div>
            </div>

            <div class="border border-slate-700 bg-[#182235] p-6">
                <div class="mb-5 border-b border-slate-700 pb-4">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Order Summary</h2>
                </div>
                <div class="space-y-4 text-sm leading-relaxed text-slate-300">
                    <p>Status / <span class="font-black uppercase tracking-widest text-white">{{ $order->status->value }}</span></p>
                    <p>Buyer / <span class="font-black uppercase tracking-widest text-white">{{ $order->user?->name ?? 'Unknown buyer' }}</span></p>
                    <p>Total / <span class="font-black uppercase tracking-widest text-white">{{ number_format((float) $order->total_amount, 2) }} MAD</span></p>
                </div>
                <div class="mt-6 space-y-3">
                    @if (session('success'))
                        <div class="border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-xs font-bold uppercase tracking-[0.16em] text-emerald-300">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="border border-rose-500/20 bg-rose-500/10 px-4 py-3 text-xs font-bold uppercase tracking-[0.16em] text-rose-300">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($order->status === \App\Enums\OrderStatus::Processing)
                        <form action="{{ route('artisan.orders.status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="shipped">
                            <button type="submit" class="inline-flex w-full items-center justify-center border border-[#10B981] bg-[#10B981]/10 px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-200 transition-all hover:bg-[#10B981] hover:text-white">
                                Mark as Shipped
                            </button>
                        </form>
                    @elseif ($order->status === \App\Enums\OrderStatus::Shipped)
                        <form action="{{ route('artisan.orders.status', $order) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="delivered">
                            <button type="submit" class="inline-flex w-full items-center justify-center border border-[#10B981] bg-[#10B981]/10 px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-emerald-200 transition-all hover:bg-[#10B981] hover:text-white">
                                Mark as Delivered
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('artisan.orders') }}" class="inline-flex w-full items-center justify-center border border-slate-500 px-5 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-200 transition-all hover:border-slate-300 hover:text-white">
                        Back to Orders
                    </a>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 xl:gap-8">
            <x-dashboard.stat-card label="Items" :value="$order->items->sum('quantity')" />
            <x-dashboard.stat-card
                label="Payment"
                :value="$order->payment_status === 'cash_on_delivery' ? 'COD' : $order->payment_status"
            />
            <x-dashboard.stat-card label="Method" :value="$order->payment_method ?: 'N/A'" />
            <x-dashboard.stat-card label="Placed" :value="$order->created_at?->format('M d')" />
        </div>

        <section class="grid gap-4 lg:gap-6 xl:grid-cols-[minmax(0,1.2fr)_20rem]">
            <div class="border border-slate-700 bg-[#182235] p-6 sm:p-8">
                <div class="mb-6 border-b border-slate-700 pb-5">
                    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Order Items</h2>
                </div>

                <div class="space-y-4">
                    @foreach ($order->items as $item)
                        <article class="border border-slate-700 bg-slate-950/30 p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0 space-y-2">
                                    <h3 class="text-sm font-black uppercase tracking-[0.16em] text-white">
                                        {{ $item->product?->name ?? 'Removed product' }}
                                    </h3>
                                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">
                                        Store / {{ $item->product?->store?->name ?? 'Unknown store' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-3 gap-3 sm:w-72">
                                    <div class="border border-slate-700 bg-[#111827] p-3 text-center">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-500">Qty</p>
                                        <p class="mt-2 text-sm text-white">{{ $item->quantity }}</p>
                                    </div>
                                    <div class="border border-slate-700 bg-[#111827] p-3 text-center">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-500">Unit</p>
                                        <p class="mt-2 text-sm text-white">{{ number_format((float) $item->unit_price, 2) }}</p>
                                    </div>
                                    <div class="border border-slate-700 bg-[#111827] p-3 text-center">
                                        <p class="text-[8px] font-black uppercase tracking-widest text-slate-500">Subtotal</p>
                                        <p class="mt-2 text-sm text-emerald-300">{{ number_format((float) $item->subtotal, 2) }}</p>
                                    </div>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>

            <div class="space-y-4">
                <section class="border border-slate-700 bg-[#182235] p-6">
                    <div class="mb-5 border-b border-slate-700 pb-4">
                        <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Shipping Address</h2>
                    </div>
                    <p class="text-sm leading-relaxed text-slate-300">
                        {{ $order->shipping_address }}
                    </p>
                </section>

                @if ($order->notes)
                    <section class="border border-slate-700 bg-[#182235] p-6">
                        <div class="mb-5 border-b border-slate-700 pb-4">
                            <h2 class="text-xs font-black uppercase tracking-[0.2em] text-[#10B981]">Order Notes</h2>
                        </div>
                        <p class="text-sm leading-relaxed text-slate-300">
                            {{ $order->notes }}
                        </p>
                    </section>
                @endif
            </div>
        </section>
    </div>
</x-dashboard.layout>
