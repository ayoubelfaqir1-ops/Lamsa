<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Order #{{ $order->id }} | {{ config('app.name', 'Lamsa') }}</title>
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
                            <h1 class="mt-2 text-3xl font-medium text-[#222222]">Order #{{ $order->id }}</h1>
                            <p class="mt-3 max-w-2xl text-sm text-gray-500">Inspect your shipment details, order items, and the artisan products included in this purchase.</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            @if ($order->status === \App\Enums\OrderStatus::Pending)
                                <a href="{{ route('orders.payment.card', ['orders' => $order->id]) }}" class="inline-flex items-center justify-center border border-black bg-black px-5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-white transition hover:bg-[#064E3B] hover:border-[#064E3B]">
                                    Continue Card Payment
                                </a>
                            @endif
                            <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center border border-black px-5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-black transition hover:bg-black hover:text-white">
                                Back to Orders
                            </a>
                        </div>
                    </div>

                    <div class="grid gap-6 lg:grid-cols-[0.75fr_1.25fr]">
                        <div class="space-y-4 border border-gray-200 bg-[#fcfcfc] p-6">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Status</p>
                                <p class="mt-2 text-lg font-medium text-[#222222]">{{ str($order->status->value)->headline() }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Total</p>
                                <p class="mt-2 text-lg font-medium text-[#222222]">{{ number_format((float) $order->total_amount, 2) }} MAD</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Payment</p>
                                <p class="mt-2 text-lg font-medium text-[#222222]">{{ str($order->payment_method)->headline() }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Shipping Address</p>
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $order->shipping_address }}</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach ($order->items as $item)
                                <div class="border border-gray-200 bg-white p-6">
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <p class="text-xl font-medium text-[#222222]">{{ $item->product?->name ?? 'Product unavailable' }}</p>
                                            <p class="mt-2 text-sm text-gray-500">{{ $item->product?->store?->name ?? 'Artisan store' }}</p>
                                        </div>
                                        <div class="text-left sm:text-right">
                                            <p class="text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">Quantity</p>
                                            <p class="mt-1 text-lg font-medium text-[#222222]">{{ $item->quantity }}</p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-6 text-sm text-gray-500">
                                        <span>Unit price: {{ number_format((float) $item->unit_price, 2) }} MAD</span>
                                        <span>Subtotal: {{ number_format((float) ($item->unit_price * $item->quantity), 2) }} MAD</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <x-footer />
        </main>
    </body>
</html>
