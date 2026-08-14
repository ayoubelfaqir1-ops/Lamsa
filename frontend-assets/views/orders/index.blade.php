<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>My Orders | {{ config('app.name', 'Lamsa') }}</title>
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
                            <h1 class="mt-2 text-3xl font-medium text-[#222222]">My Orders</h1>
                            <p class="mt-3 max-w-2xl text-sm text-gray-500">Review every order you placed, check its status, and open the full details for each artisan shipment.</p>
                        </div>
                        <a href="{{ route('profile') }}" class="inline-flex items-center justify-center border border-black px-5 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-black transition hover:bg-black hover:text-white">
                            Back to Profile
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse ($orders as $order)
                            @php
                                $statusClasses = match ($order->status->value) {
                                    'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
                                    'processing' => 'border-blue-200 bg-blue-50 text-blue-700',
                                    'shipped' => 'border-purple-200 bg-purple-50 text-purple-700',
                                    'delivered' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                    'cancelled' => 'border-rose-200 bg-rose-50 text-rose-700',
                                    default => 'border-gray-200 bg-gray-50 text-gray-700',
                                };
                                $itemsCount = $order->items->sum('quantity');
                            @endphp
                            <div class="border border-gray-200 bg-[#fcfcfc] p-6">
                                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                    <div class="space-y-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <h2 class="text-2xl font-medium text-[#222222]">Order #{{ $order->id }}</h2>
                                            <span class="inline-flex items-center border px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] {{ $statusClasses }}">
                                                {{ str($order->status->value)->headline() }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-3 text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">
                                            <span>{{ $itemsCount }} {{ \Illuminate\Support\Str::plural('item', $itemsCount) }}</span>
                                            <span>{{ number_format((float) $order->total_amount, 2) }} MAD</span>
                                            <span>{{ $order->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-sm text-gray-500">{{ $order->shipping_address }}</p>
                                    </div>

                                    <a href="{{ route('orders.show', $order) }}" class="inline-flex items-center justify-center border border-black px-4 py-3 text-[10px] font-black uppercase tracking-[0.22em] text-black transition hover:bg-black hover:text-white">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="border border-gray-200 bg-[#FAFAFA] px-8 py-16 text-center">
                                <p class="text-lg font-medium text-[#222222]">No orders yet</p>
                                <p class="mt-3 text-sm text-gray-500">You have not placed any orders yet. Start browsing products and come back here to track them.</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($orders->hasPages())
                        <div class="mt-8">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </section>

            <x-footer />
        </main>
    </body>
</html>
