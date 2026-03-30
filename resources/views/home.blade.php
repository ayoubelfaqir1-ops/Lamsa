<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Lamsa - Artisanal Heritage') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white bg-[#FAFAFA] overflow-x-hidden">

        <!-- NAVBAR -->
        <nav id="main-nav" class="fixed top-0 left-0 w-full z-50 px-6 py-6 md:px-12 flex justify-between items-center transition-all duration-500 border-b border-white/20 text-white bg-transparent">
            
            <a href="/" class="flex items-center gap-3 group text-inherit">
                <img id="logo-img" src="{{ asset('lamsa_logo.png') }}" 
                     class="w-10 h-10 md:w-11 md:h-11 object-contain transition-all duration-500 group-hover:scale-105 brightness-0 invert" alt="Lamsa Logo">
            </a>
            
            <div id="nav-links" class="hidden lg:flex gap-12 items-center text-xs font-bold uppercase tracking-widest transition-colors duration-500">
                <a href="#categories" class="hover:text-gray-300 transition-colors border-b border-transparent hover:border-white pb-1">Categories</a>
                <a href="#trending" class="hover:text-gray-300 transition-colors border-b border-transparent hover:border-white pb-1">Trending</a>
                <a href="#mission" class="hover:text-gray-300 transition-colors border-b border-transparent hover:border-white pb-1">Our Mission</a>
            </div>

            <div id="nav-actions" class="flex gap-6 items-center text-xs font-bold uppercase tracking-widest transition-colors duration-500">
                @if (Route::has('login'))
                    @auth
                        <!-- Favorites Icon -->
                        <a href="{{ route('dashboard') }}" class="hover:opacity-70 transition-opacity group" title="Favorites">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </a>
                        <!-- Cart/Panier Icon -->
                        <a href="{{ route('dashboard') }}" class="hover:opacity-70 transition-opacity group" title="Panier">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </a>
                        @unlessrole('buyer')
                            @php
                                $dashRoute = match(true) {
                                    auth()->user()->hasRole('admin') => 'admin.dashboard',
                                    auth()->user()->hasRole('artisan') => 'artisan.dashboard',
                                    default => 'dashboard'
                                };
                            @endphp
                            <a id="dash-btn" href="{{ route($dashRoute) }}" 
                               class="bg-white text-black hover:bg-gray-200 border-white px-8 py-3.5 transition-colors shadow-none cursor-pointer border">Dashboard</a>
                        @endunlessrole
                    @else
                        <a id="login-btn" href="{{ route('login') }}" 
                           class="border-white text-white hover:bg-white hover:text-black border px-8 py-3 transition-colors font-bold tracking-widest uppercase text-[10px]">Log in</a>
                    @endauth
                @endif
            </div>
        </nav>

        <!-- HERO SECTION -->
        <header class="relative w-full h-screen overflow-hidden flex flex-col justify-center items-center text-center">
            <!-- Video Background -->
            <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover z-0">
                <source src="{{ asset('lamsa-hero-section-video.mp4') }}" type="video/mp4">
            </video>
            
            <!-- Dark Overlay for readibility -->
            <div class="absolute inset-0 bg-black/30 z-10"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-black/60 z-10"></div>

            <!-- Hero Content -->
            <div class="relative z-20 px-6 max-w-5xl mx-auto flex flex-col items-center">
                <h1 class="text-5xl md:text-7xl lg:text-[10rem] font-light uppercase tracking-widest text-white leading-none">
                    Lamsa
                </h1>
                <div class="w-16 md:w-32 h-1 bg-white my-6 md:my-10"></div>
                <p class="text-xs md:text-sm uppercase tracking-[0.3em] font-medium text-gray-200 max-w-2xl leading-loose drop-shadow-xl">
                    Experience the timeless luxury of authentic Moroccan craftsmanship. Handcrafted heritage directly from the artisan's hands to your home.
                </p>
                <div class="mt-12 flex flex-col sm:flex-row gap-6">
                    <a href="#categories" class="bg-white text-black px-10 py-5 font-semibold uppercase tracking-widest text-sm hover:bg-gray-200 transition-colors shadow-2xl">
                        Discover Art
                    </a>
                    <a href="{{ route('artisan-register') }}" class="border border-white bg-black/20 backdrop-blur-sm text-white px-10 py-5 font-semibold uppercase tracking-widest text-sm hover:bg-white hover:text-black transition-colors shadow-2xl">
                        Join as Artisan
                    </a>
                </div>
            </div>
        </header>

        <!-- THE COLLECTIONS: RESTORED TABS -->
        <section id="categories" class="py-16 md:py-32 px-6 md:px-12 bg-white" x-data="{ selectedCategory: 'ceramics' }">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-center md:items-end mb-12 md:mb-20 gap-10">
                    <div class="text-center md:text-left">
                        <h2 class="text-xs font-bold uppercase tracking-[0.4em] text-[#064E3B] mb-4">Master Artisans</h2>
                        <h2 class="text-4xl md:text-6xl font-light uppercase tracking-widest text-black">The <span class="font-semibold">Collections</span></h2>
                    </div>
                    
                    <!-- Navigation -->
                    <div class="flex flex-wrap gap-6 md:gap-12 border-b border-gray-100">
                        <button @click="selectedCategory = 'ceramics'" :class="selectedCategory === 'ceramics' ? 'text-black font-bold border-black' : 'text-gray-400 font-medium border-transparent'" class="text-[10px] uppercase tracking-widest transition-all pb-4 border-b-2">
                            Ceramics
                        </button>
                        <button @click="selectedCategory = 'leather'" :class="selectedCategory === 'leather' ? 'text-black font-bold border-black' : 'text-gray-400 font-medium border-transparent'" class="text-[10px] uppercase tracking-widest transition-all pb-4 border-b-2">
                            Leather & Rugs
                        </button>
                        <button @click="selectedCategory = 'metal'" :class="selectedCategory === 'metal' ? 'text-black font-bold border-black' : 'text-gray-400 font-medium border-transparent'" class="text-[10px] uppercase tracking-widest transition-all pb-4 border-b-2">
                            Metalworks
                        </button>
                        <button @click="selectedCategory = 'woodwork'" :class="selectedCategory === 'woodwork' ? 'text-black font-bold border-black' : 'text-gray-400 font-medium border-transparent'" class="text-[10px] uppercase tracking-widest transition-all pb-4 border-b-2">
                            Woodwork
                        </button>
                        <button @click="selectedCategory = 'textiles'" :class="selectedCategory === 'textiles' ? 'text-black font-bold border-black' : 'text-gray-400 font-medium border-transparent'" class="text-[10px] uppercase tracking-widest transition-all pb-4 border-b-2">
                            Textiles
                        </button>
                    </div>
                </div>

                <!-- Collection Rows -->
                <div class="relative overflow-hidden min-h-[400px]">
                    
                    <!-- Ceramic Slider -->
                    <div x-show="selectedCategory === 'ceramics'" class="flex overflow-x-auto gap-8 pb-10 custom-scrollbar snap-x no-wrap">
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1621274403997-37aae183f5ca?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Vase">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Royal Fes Vase</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$320.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1610701596007-11502861dcfa?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Plate">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Azure Plate Set</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.2)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$150.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Bowl">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Terracotta Amphora</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.8)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$480.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1530263175402-48423ef417ca?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Mosaic">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Zellige Bowl</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[13px] text-gray-500 font-bold ml-1 tracking-tight">(4.5)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$85.00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Leather Slider -->
                    <div x-show="selectedCategory === 'leather'" style="display: none;" class="flex overflow-x-auto gap-8 pb-10 custom-scrollbar snap-x no-wrap">
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1582042171128-095fa8eb7dc7?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Leather">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Atlas Weekender</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-500 font-bold ml-1 tracking-tighter">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$520.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1531053331496-62771454ec91?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Leather">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Berber Wool Rug</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$850.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1545670723-196ed09c5a46?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Leather">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Desert Sand Pouf</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[9px] text-gray-400 font-bold ml-1 tracking-tighter">(4.4)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$120.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1590845947385-dbe7f2da5161?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Leather">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Medina Tote</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[9px] text-gray-400 font-bold ml-1 tracking-tighter">(4.7)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$210.00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Metal Slider -->
                    <div x-show="selectedCategory === 'metal'" style="display: none;" class="flex overflow-x-auto gap-8 pb-10 custom-scrollbar snap-x no-wrap">
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1611077544837-de272635a816?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Metal">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Sahara Lantern</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$185.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1599643478514-4a420804fbbe?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Metal">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Ancient Silver Bangle</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-500 font-bold ml-1 tracking-tighter">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$290.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1613945938568-6c84b4703a81?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Metal">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Copper Teapot</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.8)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$240.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1510443213568-b80c10a19611?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105" alt="Metal">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Brass Inlaid Tray</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[9px] text-gray-400 font-bold ml-1 tracking-tighter">(4.6)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$310.00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Woodwork Slider -->
                    <div x-show="selectedCategory === 'woodwork'" style="display: none;" class="flex overflow-x-auto gap-8 pb-10 custom-scrollbar snap-x no-wrap">
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1540306161947-f5e27de22ba6?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Wood">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Cedar Chest</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-4 h-4 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-500 font-bold ml-1 tracking-tighter">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$1,200.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-159643478514-4a420804fbbe?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Wood">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Moucharaby Mirror</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[9px] text-gray-400 font-bold ml-1 tracking-tighter">(4.3)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$350.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1519710164239-da123dc03ef4?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Wood">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Thuya Root Box</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.8)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$180.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1622396481328-9b1b78cdd9fd?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Wood">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Hand-carved Mask</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-5 h-5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[13px] text-gray-500 font-bold ml-1 tracking-tight">(4.5)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$220.00</p>
                            </div>
                        </div>
                    </div>

                    <!-- Textiles Slider -->
                    <div x-show="selectedCategory === 'textiles'" style="display: none;" class="flex overflow-x-auto gap-8 pb-10 custom-scrollbar snap-x no-wrap">
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1534073828943-f801091bb18c?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Textile">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Beni Ourain Pillow</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$115.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1463130489270-4bc347e30536?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Textile">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Silk Sabra Throw</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[9px] text-gray-400 font-bold ml-1 tracking-tighter">(4.1)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$85.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1544441893-675973e31985?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Textile">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Cotton Handira</h3>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-3 h-3 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[11px] text-gray-400 font-bold ml-1 tracking-tight">(4.9)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$290.00</p>
                            </div>
                        </div>
                        <div class="snap-start shrink-0 w-[280px] md:w-[350px] group cursor-pointer">
                            <div class="aspect-[4/5] overflow-hidden bg-[#F1F1F1]">
                                <img src="https://images.unsplash.com/photo-1510443213568-b80c10a19611?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Textile">
                            </div>
                            <div class="mt-6 flex flex-col items-center">
                                <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Fez Embroidery</h3>
                                <div class="flex items-center gap-1 mt-2">
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-[#064E3B]" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <svg class="w-2.5 h-2.5 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                    <span class="text-[9px] text-gray-400 font-bold ml-1 tracking-tighter">(4.7)</span>
                                </div>
                                <p class="text-[14px] text-black mt-3 font-bold tracking-widest">$140.00</p>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="mt-20 text-center">
                    <a href="#" class="inline-block bg-black text-white px-12 py-5 text-[10px] font-semibold uppercase tracking-[0.3em] hover:bg-[#064E3B] transition-colors border border-black">Explore The Entire Gallery</a>
                </div>
            </div>

            <style>
                html { scroll-behavior: auto; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale; }
                
                /* Premium Floating Scrollbar */
                .custom-scrollbar::-webkit-scrollbar { 
                    height: 2px;
                }
                
                .custom-scrollbar::-webkit-scrollbar-track { 
                    background: transparent;
                }
                
                .custom-scrollbar::-webkit-scrollbar-thumb { 
                    background: #e5e7eb;
                    transition: all 0.3s ease;
                }
                
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { 
                    background: #000; 
                }
                
                /* Aggressive Arrow Removal */
                .custom-scrollbar::-webkit-scrollbar-button { 
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }
                
                .custom-scrollbar::-webkit-scrollbar-button:start:decrement,
                .custom-scrollbar::-webkit-scrollbar-button:end:increment,
                .custom-scrollbar::-webkit-scrollbar-button:vertical:start:increment,
                .custom-scrollbar::-webkit-scrollbar-button:vertical:end:decrement {
                    display: none !important;
                    width: 0 !important;
                    height: 0 !important;
                }
                
                .no-wrap { 
                    flex-wrap: nowrap !important; 
                    -webkit-overflow-scrolling: touch;
                }
                
                .snap-x {
                    scroll-snap-type: x mandatory;
                }
            </style>
        </section>

        <!-- STATIC FEATURED GRID: TOP BIDS -->
        <section class="py-16 md:py-32 px-6 md:px-12 bg-[#FAFAFA] border-t border-gray-100">
            <div class="max-w-7xl mx-auto">
                <div class="flex justify-between items-end mb-16">
                    <div>
                        <h2 class="text-2xl md:text-4xl font-light uppercase tracking-widest text-black">Top <span class="font-semibold">Bids</span></h2>
                        <div class="w-10 h-1 bg-[#064E3B] mt-4"></div>
                    </div>
                    <a href="#" class="text-[10px] font-semibold uppercase tracking-widest text-black border-b border-black pb-1 hover:text-gray-400 hover:border-gray-400 transition-all">Go to Auctions</a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10">
                    <!-- Bid Card 1 -->
                    <div class="group/card cursor-pointer">
                        <div class="aspect-[4/5] overflow-hidden bg-white relative">
                            <img src="https://images.unsplash.com/photo-1540306161947-f5e27de22ba6?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-1000 group-hover/card:scale-105" alt="Wood">
                            <div class="absolute top-4 left-4">
                                <span class="bg-[#064E3B] text-white text-[8px] font-semibold uppercase tracking-[0.2em] px-3 py-1.5 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-white animate-pulse"></span> Live Auction
                                </span>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-sm p-3 border border-black/5">
                                <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Ends In:</p>
                                <p class="text-[11px] font-black tracking-widest text-black">02D : 14H : 33M</p>
                            </div>
                        </div>
                        <div class="mt-6 border-b border-gray-100 pb-4">
                            <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Hand-carved Cedar Chest</h3>
                            <div class="flex items-center gap-1.5 mt-2">
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <span class="text-[11px] text-gray-500 font-bold ml-1 uppercase">(24 Bids)</span>
                            </div>
                            <div class="flex justify-between items-center mt-3">
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest">Current Bid</span>
                                <span class="text-[14px] font-bold text-[#064E3B] tracking-widest">$1,250.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bid Card 2 -->
                    <div class="group/card cursor-pointer">
                        <div class="aspect-[4/5] overflow-hidden bg-white relative">
                            <img src="https://images.unsplash.com/photo-1613945938568-6c84b4703a81?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-1000 group-hover/card:scale-105" alt="Metal">
                            <div class="absolute top-4 left-4">
                                <span class="bg-[#064E3B] text-white text-[8px] font-semibold uppercase tracking-[0.2em] px-3 py-1.5 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-white animate-pulse"></span> Live Auction
                                </span>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-sm p-3 border border-black/5">
                                <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Ends In:</p>
                                <p class="text-[11px] font-black tracking-widest text-black">00D : 05H : 12M</p>
                            </div>
                        </div>
                        <div class="mt-6 border-b border-gray-100 pb-4">
                            <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Embossed Tea Ritual Set</h3>
                            <div class="flex items-center gap-1.5 mt-2">
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-gray-200" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <span class="text-[11px] text-gray-400 font-bold ml-1 uppercase">(12 Bids)</span>
                            </div>
                            <div class="flex justify-between items-center mt-3">
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest">Current Bid</span>
                                <span class="text-[14px] font-bold text-[#064E3B] tracking-widest">$340.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bid Card 3 -->
                    <div class="group/card cursor-pointer">
                        <div class="aspect-[4/5] overflow-hidden bg-white relative">
                            <img src="https://images.unsplash.com/photo-1531053331496-62771454ec91?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-1000 group-hover/card:scale-105" alt="Rug">
                            <div class="absolute top-4 left-4">
                                <span class="bg-[#064E3B] text-white text-[8px] font-semibold uppercase tracking-[0.2em] px-3 py-1.5 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-white animate-pulse"></span> Live Auction
                                </span>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-sm p-3 border border-black/5">
                                <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Ends In:</p>
                                <p class="text-[11px] font-black tracking-widest text-black">01D : 09H : 44M</p>
                            </div>
                        </div>
                        <div class="mt-6 border-b border-gray-100 pb-4">
                            <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Vintage Beni Ourain</h3>
                            <div class="flex items-center gap-1.5 mt-2">
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <span class="text-[11px] text-gray-400 font-bold ml-1 uppercase">(31 Bids)</span>
                            </div>
                            <div class="flex justify-between items-center mt-3">
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest">Current Bid</span>
                                <span class="text-[14px] font-bold text-[#064E3B] tracking-widest">$2,100.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bid Card 4 -->
                    <div class="group/card cursor-pointer">
                        <div class="aspect-[4/5] overflow-hidden bg-white relative">
                            <img src="https://images.unsplash.com/photo-1599643478514-4a420804fbbe?auto=format&fit=crop&q=80" class="w-full h-full object-cover transition-transform duration-1000 group-hover/card:scale-105" alt="Accessories">
                            <div class="absolute top-4 left-4">
                                <span class="bg-[#064E3B] text-white text-[8px] font-semibold uppercase tracking-[0.2em] px-3 py-1.5 flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 bg-white animate-pulse"></span> Live Auction
                                </span>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 bg-white/90 backdrop-blur-sm p-3 border border-black/5">
                                <p class="text-[9px] font-semibold uppercase tracking-widest text-gray-500 mb-1">Ends In:</p>
                                <p class="text-[11px] font-black tracking-widest text-black">00D : 22H : 05M</p>
                            </div>
                        </div>
                        <div class="mt-6 border-b border-gray-100 pb-4">
                            <h3 class="text-[11px] font-bold uppercase tracking-widest text-black">Tuareg Silver Amulet</h3>
                            <div class="flex items-center gap-1.5 mt-2">
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <svg class="w-4 h-4 fill-black" viewBox="0 0 20 20"><path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/></svg>
                                <span class="text-[11px] text-gray-400 font-bold ml-1 uppercase">(8 Bids)</span>
                            </div>
                            <div class="flex justify-between items-center mt-3">
                                <span class="text-[10px] text-gray-400 uppercase tracking-widest">Current Bid</span>
                                <span class="text-[14px] font-bold text-[#064E3B] tracking-widest">$420.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MISSION / ABOUT US SECTION -->
        <section id="mission" class="py-16 md:py-32 px-6 md:px-12 bg-black text-white relative overflow-hidden">
            <!-- Decorative Accent -->
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-[30vh] h-[30vh] bg-[#064E3B] opacity-50 blur-[150px] pointer-events-none"></div>
            
            <div class="max-w-6xl mx-auto flex flex-col lg:flex-row gap-16 lg:gap-24 items-center relative z-10">
                <div class="w-full lg:w-1/2">
                    <h2 class="text-4xl md:text-5xl lg:text-7xl font-light uppercase tracking-widest leading-none mb-8">
                        Preserving <br><span class="font-bold text-[#F0FDF4] italic">Legacies</span>
                    </h2>
                    <div class="w-16 h-1 bg-[#064E3B] mb-10"></div>
                    <p class="text-sm text-gray-300 leading-loose font-medium tracking-wide uppercase mb-6 drop-shadow-md">
                        For centuries, the medinas of Morocco have hummed with the sound of hammers striking copper, looms weaving silk, and chisels shaping cedar.
                    </p>
                    <p class="text-sm text-gray-400 leading-loose font-medium tracking-wide uppercase">
                        Lamsa bridges this historic divide. We provide an exclusive platform for true Moroccan creators to showcase their art directly to a global audience that demands authentic, unadulterated craftsmanship rather than factory imitations.
                    </p>
                    <div class="mt-14">
                        <a href="{{ route('artisan-register') }}" class="inline-block border border-white bg-transparent px-10 py-5 text-xs font-black uppercase tracking-widest hover:bg-white hover:text-black transition-colors">
                            Partner With Us As Artisan
                        </a>
                    </div>
                </div>
                
                <div class="w-full lg:w-1/2 h-[500px] md:h-[600px] border border-white/20 p-4 relative group">
                    <!-- Subtle overlay border animation effect -->
                    <div class="absolute inset-0 border border-white scale-95 opacity-0 group-hover:scale-100 group-hover:opacity-100 transition-all duration-700 pointer-events-none z-20"></div>
                    <img src="{{ asset('artisan-bg.jpg') }}" alt="Artisan Crafting" class="w-full h-full object-cover filter brightness-75 grayscale group-hover:grayscale-0 transition-all duration-1000">
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="bg-[#FAFAFA] pt-16 md:pt-32 pb-16 px-6 md:px-12 border-t border-black">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-16 mb-24 border-b-2 border-black pb-24">
                
                <div class="lg:col-span-4">
                    <a href="/" class="flex items-center gap-4 mb-8 group">
                        <img src="{{ asset('lamsa_logo.png') }}" class="w-16 h-16 object-contain mix-blend-multiply transition-transform duration-500 group-hover:-rotate-6" alt="Lamsa Logo">
                        <span class="font-semibold text-3xl tracking-tighter uppercase text-black">Lamsa</span>
                    </a>
                    <p class="text-[10px] sm:text-xs text-gray-500 font-medium uppercase tracking-widest leading-loose max-w-sm">
                        Authentic Moroccan craftsmanship delivered to your doorstep. Cultivating heritage, securing authenticity.
                    </p>
                </div>

                <div class="lg:col-span-2">
                    <h4 class="text-xs font-black uppercase tracking-widest text-black mb-8">Directory</h4>
                    <ul class="space-y-5 text-[10px] sm:text-xs uppercase tracking-widest text-gray-500 font-bold">
                        <li><a href="#" class="hover:text-black transition-colors flex items-center gap-2"><span class="w-2 h-2 border border-black inline-block"></span> Ceramics</a></li>
                        <li><a href="#" class="hover:text-black transition-colors flex items-center gap-2"><span class="w-2 h-2 border border-black inline-block"></span> Woodwork</a></li>
                        <li><a href="#" class="hover:text-black transition-colors flex items-center gap-2"><span class="w-2 h-2 border border-black inline-block"></span> Leather</a></li>
                        <li><a href="#" class="hover:text-black transition-colors flex items-center gap-2"><span class="w-2 h-2 border border-black inline-block"></span> Jewelry</a></li>
                    </ul>
                </div>

                <div class="lg:col-span-2">
                    <h4 class="text-xs font-black uppercase tracking-widest text-black mb-8">Platform</h4>
                    <ul class="space-y-5 text-[10px] sm:text-xs uppercase tracking-widest text-gray-500 font-bold">
                        <li><a href="#mission" class="hover:text-black transition-colors">Our Mission</a></li>
                        <li><a href="{{ route('artisan-register') }}" class="hover:text-[#064E3B] transition-colors">Artisan Portal</a></li>
                        <li><a href="#" class="hover:text-black transition-colors">Authenticity Check</a></li>
                        <li><a href="#" class="hover:text-black transition-colors">Terms of Service</a></li>
                    </ul>
                </div>

                <div class="lg:col-span-4">
                    <h4 class="text-xs font-black uppercase tracking-widest text-black mb-8">The Private Gallery</h4>
                    <p class="text-[10px] sm:text-xs text-gray-500 font-bold uppercase tracking-widest leading-loose mb-6">
                        Subscribe for early access to curated collections and exclusive artisan stories directly in your inbox.
                    </p>
                    <form class="flex flex-col sm:flex-row shadow-none border border-black bg-white group hover:border-[#064E3B] transition-colors">
                        <input type="email" placeholder="YOUR EMAIL ADDRESS" class="w-full px-5 py-4 bg-transparent outline-none focus:ring-0 border-none text-[10px] sm:text-xs font-bold uppercase tracking-widest placeholder-gray-400">
                        <button type="submit" class="bg-black text-white px-8 py-4 sm:border-l border-black text-xs font-black uppercase tracking-widest hover:bg-[#064E3B] transition-colors whitespace-nowrap">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>

            <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center opacity-80">
                <p class="text-[10px] font-black uppercase tracking-widest text-black mb-6 md:mb-0">
                    &copy; {{ date('Y') }} Lamsa Market. All Rights Reserved.
                </p>
                <div class="flex gap-8">
                    <a href="#" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-[#064E3B] transition-colors">Instagram</a>
                    <a href="#" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-[#064E3B] transition-colors">Pinterest</a>
                    <a href="#" class="text-[10px] font-black uppercase tracking-widest text-black hover:text-[#064E3B] transition-colors">Facebook</a>
                </div>
            </div>
        </footer>

        <script>
            function updateNav() {
                const nav = document.getElementById('main-nav');
                const logo = document.getElementById('logo-img');
                const dashBtn = document.getElementById('dash-btn');
                const loginBtn = document.getElementById('login-btn');
                
                if (window.scrollY > 50) {
                    nav.classList.remove('bg-transparent', 'text-white', 'border-white/20');
                    nav.classList.add('bg-white', 'text-black', 'border-gray-100', 'shadow-sm');
                    logo.classList.remove('invert');
                    
                    if (dashBtn) {
                        dashBtn.classList.remove('bg-white', 'text-black', 'border-white');
                        dashBtn.classList.add('bg-black', 'text-white', 'border-black');
                    }
                    if (loginBtn) {
                        loginBtn.classList.remove('border-white', 'text-white');
                        loginBtn.classList.add('border-black', 'text-black');
                    }
                } else {
                    nav.classList.add('bg-transparent', 'text-white', 'border-white/20');
                    nav.classList.remove('bg-white', 'text-black', 'border-gray-100', 'shadow-sm');
                    logo.classList.add('invert');
                    
                    if (dashBtn) {
                        dashBtn.classList.add('bg-white', 'text-black', 'border-white');
                        dashBtn.classList.remove('bg-black', 'text-white', 'border-black');
                    }
                    if (loginBtn) {
                        loginBtn.classList.add('border-white', 'text-white');
                        loginBtn.classList.remove('border-black', 'text-black');
                    }
                }
            }
            window.addEventListener('scroll', updateNav);
            window.addEventListener('load', updateNav);
        </script>
    </body>
</html>
