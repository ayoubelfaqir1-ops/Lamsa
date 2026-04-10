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
    <body class="font-sans antialiased bg-[#080b14] text-slate-200 selection:bg-[#064E3B] selection:text-white">
        <div class="min-h-screen flex">
            <aside class="fixed z-50 flex h-full w-72 flex-col border-r border-white/5 bg-[#050505] text-white">
                <div class="px-8 py-10">
                    <a href="{{ route('home') }}" class="group flex items-center gap-3">
                        <img src="{{ asset('lamsa_logo.png') }}" class="h-10 w-10 object-contain invert brightness-0 transition-transform duration-500 group-hover:-rotate-12" alt="Lamsa Logo">
                        <span class="text-xl font-black uppercase tracking-tighter text-white">Lamsa</span>
                    </a>
                </div>
                <nav class="flex-1 overflow-y-auto pt-4">
                    {{ $sidebar ?? '' }}
                </nav>
                <div class="mt-auto border-t border-slate-800 bg-[#0f172a]/50 px-6 py-8">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="group relative">
                            <div class="flex h-10 w-10 items-center justify-center bg-[#064E3B] text-xs font-black uppercase ring-2 ring-white/10 transition-all group-hover:ring-[#064E3B]">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="absolute -bottom-1 -right-1 h-3 w-3 border-2 border-[#1e293b] bg-emerald-500"></div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-[10px] font-black uppercase tracking-[0.1em] text-white">{{ auth()->user()->name }}</p>
                            <p class="truncate text-[8px] font-bold uppercase leading-tight tracking-widest text-slate-500">{{ auth()->user()->getRoleNames()->first() }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('profile') }}" class="flex items-center justify-center gap-2 border border-slate-700 bg-slate-800/50 py-2 text-[9px] font-black uppercase tracking-widest text-slate-400 transition-all hover:bg-slate-800 hover:text-white">
                            <x-dashboard.icon name="user" class="h-3 w-3" />
                            Profile
                        </a>
                        <button onclick="document.getElementById('logout-form').submit()" class="flex items-center justify-center gap-2 border border-slate-700 bg-slate-800/50 py-2 text-[9px] font-black uppercase tracking-widest text-slate-400 transition-all hover:bg-rose-500/20 hover:text-rose-400">
                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            Exit
                        </button>
                    </div>
                </div>
            </aside>
            <main class="ml-72 flex-1">
                <header class="sticky top-0 z-40 flex h-20 items-center justify-between border-b border-slate-800 bg-[#0f172a]/80 px-12 backdrop-blur-md">
                    <div class="flex items-center gap-3">
                        <div class="h-1.5 w-1.5 bg-[#064E3B]"></div>
                        <h1 class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">{{ $headerTitle ?? $header_title ?? 'Overview' }}</h1>
                    </div>
                    <div class="flex items-center gap-8">
                        <button class="relative text-slate-500 transition-colors hover:text-white">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            <span class="absolute -right-1 -top-1 h-2 w-2 border border-[#0f172a] bg-rose-500"></span>
                        </button>
                        <div class="h-8 w-px bg-slate-800"></div>
                        <div class="flex flex-col items-end">
                            <span class="text-[9px] font-black uppercase leading-tight tracking-widest text-white">{{ auth()->user()->name }}</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest text-[#064E3B] opacity-70">Active Session</span>
                        </div>
                    </div>
                </header>
                <div class="min-h-[calc(100vh-80px)] p-12">
                    {{ $slot }}
                </div>
            </main>
        </div>
        @livewireScripts
    </body>
</html>
