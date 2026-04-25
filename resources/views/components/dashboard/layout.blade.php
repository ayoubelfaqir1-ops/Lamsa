<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Lamsa') }} - Dashboard</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
            .livewire-progress-bar { height: 2px !important; background: #10B981 !important; }
            @keyframes shimmer {
                0% { background-position: -1000px 0; }
                100% { background-position: 1000px 0; }
            }
            .shimmer {
                background: linear-gradient(to right, #1e293b 8%, #334155 18%, #1e293b 33%);
                background-size: 1000px 100%;
                animation: shimmer 2s infinite linear;
            }
        </style>
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-[#0f172a] text-slate-200 selection:bg-[#10B981] selection:text-white">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen xl:flex">
            <div
                x-cloak
                x-show="sidebarOpen"
                x-transition.opacity
                class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm xl:hidden"
                @click="sidebarOpen = false"
            ></div>
            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-800 bg-[#0f172a] transition-transform duration-300 xl:translate-x-0 shadow-xl"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <!-- Logo area -->
                <div class="flex h-14 items-center gap-3 px-6 border-b border-slate-800/60">
                    <a href="{{ route('home') }}" class="group flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-none bg-gradient-to-br from-[#10B981] to-emerald-700 shadow-md">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url('site/branding/lamsa_logo.png') }}" class="h-5 w-5 object-contain invert brightness-0" alt="Lamsa Logo">
                        </div>
                        <span class="text-xl font-bold tracking-tight text-white">Lamsa</span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-1">
                    {{ $sidebar ?? '' }}
                </nav>

                <!-- User Profile area -->
                <div class="mt-auto border-t border-slate-800/60 bg-slate-900/30 p-4">
                    <div class="flex items-center gap-3 p-2 rounded-none transition-colors hover:bg-slate-800/50">
                        <div class="relative">
                            <div class="flex h-9 w-9 items-center justify-center rounded-none bg-slate-800 text-sm font-semibold text-white ring-1 ring-slate-700">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="absolute -bottom-1 -right-1 h-2.5 w-2.5 rounded-none border border-[#0f172a] bg-emerald-500"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-xs text-slate-400 capitalize">{{ str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? 'User') }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-3 grid grid-cols-2 gap-2 px-2">
                        <a href="{{ route('profile') }}" class="flex items-center justify-center gap-2 rounded-none border border-slate-700/50 bg-slate-800/50 py-2 text-xs font-medium text-slate-300 transition-colors hover:bg-slate-700 hover:text-white">
                            <x-dashboard.icon name="user" class="h-3.5 w-3.5" />
                            Profile
                        </a>
                        <button type="button" onclick="document.getElementById('logout-form').submit()" class="flex items-center justify-center gap-2 rounded-none border border-slate-700/50 bg-slate-800/50 py-2 text-xs font-medium text-slate-300 transition-colors hover:bg-rose-500/10 hover:text-rose-400">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Logout
                        </button>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                    </div>
                </div>
            </aside>

            <main class="min-w-0 flex-1 xl:ml-64 bg-[#020617]">
                <!-- Header -->
                <header class="sticky top-0 z-30 flex h-14 items-center justify-between border-b border-slate-800/60 bg-[#020617]/80 px-4 sm:px-6 lg:px-8 backdrop-blur-md">
                    <div class="flex items-center gap-4">
                        <button
                            type="button"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-none border border-slate-700 bg-slate-800/50 text-slate-400 transition hover:bg-slate-700 hover:text-white xl:hidden"
                            @click="sidebarOpen = true"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 class="text-sm font-semibold text-slate-200">{{ $headerTitle ?? $header_title ?? 'Overview' }}</h1>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <button class="relative hidden text-slate-400 transition-colors hover:text-white sm:block">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute top-0 right-0 block h-2 w-2 rounded-none ring-1 ring-[#020617] bg-emerald-500"></span>
                        </button>
                        <div class="hidden h-5 w-px bg-slate-700 sm:block"></div>
                        <div class="flex items-center gap-3">
                            <div class="hidden flex-col items-end sm:flex">
                                <span class="text-sm font-medium text-white">{{ auth()->user()->name }}</span>
                                <span class="text-xs text-slate-400 capitalize">{{ str_replace('_', ' ', auth()->user()->getRoleNames()->first() ?? 'User') }}</span>
                            </div>
                            <div class="h-8 w-8 rounded-none bg-slate-800 flex items-center justify-center text-sm font-semibold text-white ring-1 ring-slate-700">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                        </div>
                    </div>
                </header>

                <div class="min-h-[calc(100vh-3.5rem)] p-4 sm:p-6 lg:p-8 xl:p-10">
                    {{ $slot }}
                </div>
            </main>
        </div>
        @livewireScripts
    </body>
</html>
