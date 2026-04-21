<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Profile | {{ config('app.name', 'Lamsa') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
        </style>
    </head>
    <body class="min-h-screen bg-[#F3F4F6] font-sans antialiased text-gray-900 selection:bg-[#064E3B] selection:text-white">
        <x-navbar />

        <main class="pt-[96px] pb-24">
            <section class="border-b border-gray-200 bg-white">
                <div class="mx-auto max-w-7xl px-6 py-10 md:px-12">
                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center gap-5">
                            <div class="flex h-16 w-16 items-center justify-center bg-black text-2xl font-black text-white">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">Account settings</p>
                                <h1 class="mt-2 text-3xl font-medium text-[#222222]">{{ $user->name }}</h1>
                                <p class="mt-2 text-sm text-gray-500">{{ $user->email }} - Member since {{ $user->created_at->format('Y') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                            <div class="border border-gray-200 bg-[#FAFAFA] px-5 py-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Orders</p>
                                <p class="mt-2 text-2xl font-black text-black">{{ $stats['orders'] }}</p>
                            </div>
                            <div class="border border-gray-200 bg-[#FAFAFA] px-5 py-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Active</p>
                                <p class="mt-2 text-2xl font-black text-black">{{ $stats['active_orders'] }}</p>
                            </div>
                            <div class="border border-gray-200 bg-[#FAFAFA] px-5 py-4">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400">Bids</p>
                                <p class="mt-2 text-2xl font-black text-black">{{ $stats['bids'] }}</p>
                            </div>
                            <div class="bg-[#064E3B] px-5 py-4 text-white">
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-emerald-100">Closed</p>
                                <p class="mt-2 text-2xl font-black">{{ $stats['won_bids'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto mt-10 max-w-7xl px-6 md:px-12">
                <div class="mt-10 grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
                    <div class="space-y-8">
                        <div class="border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 bg-gray-50 px-8 py-6">
                                <h2 class="text-sm font-black uppercase tracking-[0.22em] text-gray-900">Personal Information</h2>
                            </div>
                            <div class="p-8 md:p-10">
                                <livewire:profile.update-profile-information-form />
                            </div>
                        </div>

                        <div class="border border-gray-200 bg-white shadow-sm">
                            <div class="border-b border-gray-100 bg-gray-50 px-8 py-6">
                                <h2 class="text-sm font-black uppercase tracking-[0.22em] text-gray-900">Password & Access</h2>
                            </div>
                            <div class="p-8 md:p-10">
                                <livewire:profile.update-password-form />
                            </div>
                        </div>

                        <div class="border border-red-100 bg-white shadow-sm">
                            <div class="border-b border-red-100 bg-red-50/40 px-8 py-6">
                                <h2 class="text-sm font-black uppercase tracking-[0.22em] text-red-600">Danger Zone</h2>
                            </div>
                            <div class="p-8 md:p-10">
                                <p class="mb-8 text-xs font-medium uppercase tracking-[0.18em] text-gray-500">Deleting your account removes your access and cannot be undone.</p>
                                <livewire:profile.delete-user-form />
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        <div class="border border-gray-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-8 py-5">
                                <h2 class="text-xs font-black uppercase tracking-[0.22em] text-gray-900">Recent Orders</h2>
                                <a href="{{ route('orders.index') }}" class="text-[10px] font-black uppercase tracking-[0.18em] text-[#064E3B] hover:underline">View All</a>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @forelse ($orders->take(3) as $order)
                                    <a href="{{ route('orders.show', $order) }}" class="block px-8 py-6 transition hover:bg-gray-50">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-sm font-bold text-gray-900">Order #{{ $order->id }}</p>
                                                <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">{{ $order->created_at->format('M d, Y') }}</p>
                                            </div>
                                            <p class="text-sm font-black text-black">{{ number_format((float) $order->total_amount, 2) }} MAD</p>
                                        </div>
                                        <p class="mt-3 text-sm text-gray-500">{{ str($order->status->value)->headline() }}</p>
                                    </a>
                                @empty
                                    <div class="px-8 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-500">No orders yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="border border-gray-200 bg-white shadow-sm">
                            <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50 px-8 py-5">
                                <h2 class="text-xs font-black uppercase tracking-[0.22em] text-gray-900">Recent Bids</h2>
                                <a href="{{ route('bids.my') }}" class="text-[10px] font-black uppercase tracking-[0.18em] text-[#064E3B] hover:underline">View All</a>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @forelse ($bids->take(3) as $bid)
                                    <a href="{{ $bid->auction ? route('auctions.show', $bid->auction) : route('bids.my') }}" class="block px-8 py-6 transition hover:bg-gray-50">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-bold text-gray-900">{{ $bid->auction?->name ?? 'Auction unavailable' }}</p>
                                                <p class="mt-1 text-[11px] font-bold uppercase tracking-[0.18em] text-gray-400">{{ $bid->created_at->diffForHumans() }}</p>
                                            </div>
                                            <p class="whitespace-nowrap text-sm font-black text-[#064E3B]">{{ number_format((float) $bid->amount, 2) }} MAD</p>
                                        </div>
                                    </a>
                                @empty
                                    <div class="px-8 py-12 text-center">
                                        <p class="text-sm font-medium text-gray-500">No bids yet.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <x-footer />

        @livewireScripts
    </body>
</html>
