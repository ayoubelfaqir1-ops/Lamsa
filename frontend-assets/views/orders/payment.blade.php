<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Card Payment | {{ config('app.name', 'Lamsa') }}</title>
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
                <div class="mx-auto max-w-6xl px-6 py-10 md:px-12">
                    <div class="mb-8 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">Checkout flow</p>
                            <h1 class="mt-2 text-3xl font-medium text-[#222222]">Card Payment for {{ $orders->count() }} {{ \Illuminate\Support\Str::plural('Order', $orders->count()) }}</h1>
                            <p class="mt-3 max-w-2xl text-sm text-gray-500">These orders were created with a <span class="font-semibold text-[#222222]">pending</span> status and are waiting for card confirmation before moving to <span class="font-semibold text-[#222222]">processing</span>.</p>
                        </div>
                        <a href="{{ route('orders.index') }}" class="inline-flex items-center justify-center border border-black px-5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-black transition hover:bg-black hover:text-white">
                            My Orders
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                        <div class="space-y-4 border border-gray-200 bg-[#fcfcfc] p-6">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Pending orders</p>
                                <p class="mt-2 text-lg font-medium text-[#222222]">{{ $orders->count() }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Grand total</p>
                                <p class="mt-2 text-lg font-medium text-[#222222]">{{ number_format($grandTotal, 2) }} MAD</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Payment methods</p>
                                <p class="mt-2 text-sm text-gray-600">Card</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">How it works</p>
                                <p class="mt-2 text-sm leading-6 text-gray-600">This step confirms the whole batch as paid and moves it from pending to processing.</p>
                            </div>
                        </div>

                        <div class="border border-gray-200 bg-white p-6">
                            <div class="border-b border-gray-100 pb-6">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#064E3B]">Payment batch</p>
                                <h2 class="mt-3 text-2xl font-medium text-[#222222]">Orders included in this payment</h2>
                            </div>

                            <div class="mt-6 space-y-4">
                                @foreach ($orders as $order)
                                    <div class="border border-gray-100 bg-[#FAFAFA] p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <p class="text-sm font-bold text-[#222222]">Order #{{ $order->id }}</p>
                                                <p class="mt-1 text-[10px] font-black uppercase tracking-[0.18em] text-gray-400">{{ str($order->status->value)->headline() }}</p>
                                            </div>
                                            <p class="text-sm font-black text-[#222222]">{{ number_format((float) $order->total_amount, 2) }} MAD</p>
                                        </div>
                                        <p class="mt-3 text-sm text-gray-500">{{ $order->shipping_address }}</p>
                                        <div class="mt-4 flex flex-wrap gap-3">
                                            <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center justify-center border border-black px-4 py-2 text-[10px] font-black uppercase tracking-[0.22em] text-black transition hover:bg-black hover:text-white">
                                                View Order
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-8 border-t border-gray-100 pt-6">
                                <form action="{{ route('orders.payment.card.process') }}" method="POST" class="space-y-5">
                                    @csrf
                                    <input type="hidden" name="orders" value="{{ $orders->pluck('id')->implode(',') }}">

                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.22em] text-[#064E3B]">Card confirmation</p>
                                        <div class="mt-4 border border-gray-200 p-4">
                                            <p class="text-sm font-bold text-[#222222]">Card</p>
                                            <p class="mt-1 text-sm leading-6 text-gray-500">Use this button to confirm the pending batch as paid. When you connect a real gateway later, this is the branch you’ll replace.</p>
                                        </div>
                                    </div>

                                    <button type="submit" class="inline-flex items-center justify-center border border-black bg-black px-5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-white transition hover:border-[#064E3B] hover:bg-[#064E3B]">
                                        Confirm Card Payment
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <x-footer />
        </main>
    </body>
</html>
