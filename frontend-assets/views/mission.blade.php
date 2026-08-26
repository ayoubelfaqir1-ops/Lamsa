<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Lamsa - Our Mission') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <!-- AOS Animation Library -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
            html { scroll-behavior: smooth; }
            .hero-bg {
                background: linear-gradient(to bottom, rgba(0,0,0,0.8), rgba(6,78,59,0.85)), url('https://images.unsplash.com/photo-1540306161947-f5e27de22ba6?auto=format&fit=crop&q=80') center/cover fixed;
            }
            .pattern-bg {
                background-image: radial-gradient(#064E3B 1px, transparent 1px);
                background-size: 32px 32px;
                background-color: #FAFAFA;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white bg-[#FAFAFA] overflow-x-hidden">
        @php
            $missionHeroImageUrl = \Illuminate\Support\Facades\Storage::url('site/images/mission_hero_generations.png');
            $artisanBannerImageUrl = \Illuminate\Support\Facades\Storage::url('site/branding/artisan-bg.jpg');
        @endphp

        <x-navbar />

        <!-- CINEMATIC MISSION HERO -->
        <header class="relative bg-black text-white h-screen flex flex-col overflow-hidden">
            <!-- Background Typography Watermark -->
            <div class="absolute inset-0 flex items-center justify-center opacity-[0.03] select-none pointer-events-none">
                <span class="text-[35vw] font-black uppercase tracking-tighter leading-none">HERITAGE</span>
            </div>

            <div class="relative z-10 flex flex-col lg:flex-row w-full h-full pt-32 lg:pt-40">
                <!-- Left Content: The Vision Statement -->
                <div class="w-full lg:w-3/5 flex flex-col justify-start px-8 md:px-16 lg:px-24" data-aos="fade-right" data-aos-duration="1500">
                    <div class="max-w-3xl">
                        <span class="text-[10px] font-bold uppercase tracking-[0.6em] text-[#10B981] mb-8 block">Our Purpose & Mission</span>
                        
                        <h1 class="text-5xl md:text-7xl lg:text-8xl font-light uppercase tracking-tighter leading-[0.9] mb-10">
                            Preserving <br><span class="font-bold italic text-emerald-100">the soul</span> of <br>Moroccan craft.
                        </h1>

                        <div class="w-20 h-[1px] bg-white/30 mb-10"></div>

                        <p class="text-base md:text-lg text-gray-400 uppercase tracking-widest leading-loose max-w-xl mb-12">
                            Lamsa is a bridge across time—connecting the ancient mastery of our ancestors with the digital heartbeat of the modern world.
                        </p>

                        <div class="flex items-center gap-6">
                            <a href="#vision" class="text-[10px] font-black uppercase tracking-[0.4em] px-8 py-4 bg-white text-black hover:bg-[#10B981] hover:text-white transition-all">
                                Read Our Story
                            </a>
                            <div class="w-12 h-[1px] bg-white/20"></div>
                            <span class="text-[9px] font-bold uppercase tracking-[0.4em] text-white/40 italic">Est. Traditions</span>
                        </div>
                    </div>
                </div>

                <!-- Right Content: The Visual Card -->
                <div class="w-full lg:w-2/5 relative flex items-center justify-center p-8 md:p-16 lg:p-12 bg-neutral-900/30">
                    <div class="relative w-full max-w-md aspect-[3/4] group overflow-hidden shadow-2xl" data-aos="fade-left" data-aos-duration="1500">
                        <img src="{{ $missionHeroImageUrl }}" alt="Generations of Craft" class="absolute inset-0 w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-[2000ms] group-hover:scale-110">
                        
                        <!-- Premium Overlays -->
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-transparent transition-colors duration-1000"></div>
                        <div class="absolute inset-0 border-[12px] border-white/5 pointer-events-none z-20"></div>
                        
                        <!-- Floating Caption -->
                        <div class="absolute -bottom-4 -left-4 bg-[#064E3B] p-6 shadow-xl hidden md:block">
                            <p class="text-[8px] font-black uppercase tracking-[0.5em] text-white/60 mb-2">The Lineage</p>
                            <p class="text-[10px] text-white font-medium tracking-widest uppercase">Generations of Mastery</p>
                        </div>
                    </div>

                    <!-- Framing Line Decorative -->
                    <div class="absolute top-1/2 right-0 w-1/2 h-[1px] bg-white/5 z-0"></div>
                </div>
            </div>
        </header>

        <!-- STATEMENT & VISION SECTION -->
        <section class="py-24 md:py-32 px-6 md:px-12 bg-white relative">
            <div class="max-w-4xl mx-auto text-center" data-aos="fade-up">
                <p class="text-2xl md:text-4xl font-light leading-snug tracking-wider text-black mb-16 uppercase">
                    <strong class="font-bold text-[#064E3B]">Our mission</strong> is to give every Moroccan artisan a dignified, modern place to sell their craft &mdash; and every buyer a trusted path to the authentic.
                </p>
            </div>
            
            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 lg:gap-24 items-center mt-16">
                <div data-aos="fade-right" data-aos-delay="200" class="text-[11px] text-gray-600 leading-loose uppercase tracking-widest font-medium space-y-6">
                    <p>For centuries, Moroccan craftsmanship has told the story of our people &mdash; in the geometry of a zellige tile, the texture of hand-woven wool, the smell of tanned leather in the medina.</p>
                    <p>Yet most of the artisans behind these objects remain invisible, underpaid, and disconnected from the people who love what they make.</p>
                    <div class="h-px w-24 bg-black/10 my-8"></div>
                    <p class="text-black font-bold">Lamsa changes that. We built a platform where artisans own their presence, set their prices, and reach customers directly &mdash; without a middleman taking most of the value.</p>
                    <p>And where buyers, whether local or from across the world, can shop with confidence, knowing exactly who made what they're holding, and why it matters.</p>
                </div>
                
                <div data-aos="fade-left" data-aos-delay="400" class="relative">
                    <div class="aspect-square bg-gray-100 overflow-hidden relative group">
                        <img src="{{ $artisanBannerImageUrl }}" alt="Artisan Vision" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-105 filter grayscale hover:grayscale-0">
                        <div class="absolute inset-0 bg-[#064E3B]/80 p-8 flex flex-col justify-center items-center text-center opacity-0 group-hover:opacity-100 transition-opacity duration-700 backdrop-blur-sm">
                            <h3 class="text-white text-[10px] uppercase tracking-[0.4em] font-bold mb-4">Our Vision</h3>
                            <p class="text-white text-xl md:text-3xl font-light uppercase tracking-widest leading-snug">
                                "A Morocco where craftsmanship is a <span class="font-bold italic">viable career</span>, not a dying trade."
                            </p>
                        </div>
                    </div>
                    <!-- Decorative elements -->
                    <div class="absolute -bottom-6 -left-6 w-24 h-24 border-b-2 border-l-2 border-[#064E3B] z-[-1]"></div>
                    <div class="absolute -top-6 -right-6 w-24 h-24 border-t-2 border-r-2 border-[#064E3B] z-[-1]"></div>
                </div>
            </div>

            <div class="max-w-4xl mx-auto mt-24 text-center" data-aos="fade-up">
                <p class="text-[11px] text-gray-500 uppercase tracking-widest leading-loose font-medium mb-4">
                    We envision a future where a young weaver in Azrou can grow a thriving online business. Where a zellige master in Fez is as discoverable as any global brand. Where "made in Morocco" is not just a label &mdash; it's a guarantee of quality, story, and fair value.
                </p>
                <p class="text-xs font-bold text-black uppercase tracking-[0.3em]">Lamsa is the infrastructure for that future.</p>
            </div>
        </section>

        <!-- VALUES SECTION -->
        <section class="py-24 md:py-32 px-6 md:px-12 bg-black text-white relative pattern-bg">
            <!-- Dark overlay for pattern -->
            <div class="absolute inset-0 bg-black/90"></div>
            
            <div class="max-w-7xl mx-auto relative z-10">
                <div class="text-center mb-24" data-aos="fade-up">
                    <span class="text-[#10B981] font-bold tracking-[0.4em] uppercase text-[10px] mb-4 block">The Foundation</span>
                    <h2 class="text-4xl md:text-6xl font-light uppercase tracking-widest">Our <span class="font-bold">Values</span></h2>
                    <div class="w-10 h-1 bg-[#10B981] mx-auto mt-6"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-20">
                    <!-- Value 1 -->
                    <div class="group" data-aos="fade-up" data-aos-delay="100">
                        <div class="text-[#10B981] text-5xl font-light mb-6 opacity-50 group-hover:opacity-100 group-hover:-translate-y-2 transition-all duration-500">01</div>
                        <h3 class="text-lg font-bold uppercase tracking-widest mb-4 border-b border-white/20 pb-4">Authenticity First</h3>
                        <p class="text-[11px] text-gray-400 uppercase tracking-widest leading-loose font-medium">
                            We do not sell copies. Every artisan on Lamsa is vetted, every product is hand-crafted. We take the integrity of Moroccan craft seriously &mdash; because our artisans deserve nothing less.
                        </p>
                    </div>

                    <!-- Value 2 -->
                    <div class="group" data-aos="fade-up" data-aos-delay="200">
                        <div class="text-[#10B981] text-5xl font-light mb-6 opacity-50 group-hover:opacity-100 group-hover:-translate-y-2 transition-all duration-500">02</div>
                        <h3 class="text-lg font-bold uppercase tracking-widest mb-4 border-b border-white/20 pb-4">Fair Value for the Creator</h3>
                        <p class="text-[11px] text-gray-400 uppercase tracking-widest leading-loose font-medium">
                            The person who makes the thing should be the one who benefits most from selling it. Our platform is designed to put artisans in control &mdash; of their shop, their pricing, and their story.
                        </p>
                    </div>

                    <!-- Value 3 -->
                    <div class="group" data-aos="fade-up" data-aos-delay="300">
                        <div class="text-[#10B981] text-5xl font-light mb-6 opacity-50 group-hover:opacity-100 group-hover:-translate-y-2 transition-all duration-500">03</div>
                        <h3 class="text-lg font-bold uppercase tracking-widest mb-4 border-b border-white/20 pb-4">Trust, Built Into Everything</h3>
                        <p class="text-[11px] text-gray-400 uppercase tracking-widest leading-loose font-medium">
                            Buyers deserve to know what they're getting. Artisans deserve to know they'll be paid. We built Lamsa with secure payments, transparent commissions, and a review process that keeps quality high on both sides.
                        </p>
                    </div>

                    <!-- Value 4 -->
                    <div class="group" data-aos="fade-up" data-aos-delay="400">
                        <div class="text-[#10B981] text-5xl font-light mb-6 opacity-50 group-hover:opacity-100 group-hover:-translate-y-2 transition-all duration-500">04</div>
                        <h3 class="text-lg font-bold uppercase tracking-widest mb-4 border-b border-white/20 pb-4">Roots + Reach</h3>
                        <p class="text-[11px] text-gray-400 uppercase tracking-widest leading-loose font-medium">
                            We are proudly Moroccan. But craft has no borders. Lamsa bridges the local medina and the global market &mdash; preserving cultural heritage while opening new doors.
                        </p>
                    </div>

                    <!-- Value 5 -->
                    <div class="group" data-aos="fade-up" data-aos-delay="500">
                        <div class="text-[#10B981] text-5xl font-light mb-6 opacity-50 group-hover:opacity-100 group-hover:-translate-y-2 transition-all duration-500">05</div>
                        <h3 class="text-lg font-bold uppercase tracking-widest mb-4 border-b border-white/20 pb-4">Community Over Competition</h3>
                        <p class="text-[11px] text-gray-400 uppercase tracking-widest leading-loose font-medium">
                            Lamsa is not a race to the bottom on price. We're building a community of makers and appreciators &mdash; one where reputation, craft quality, and relationships matter more than algorithms.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- WHO WE SERVE -->
        <section class="py-24 md:py-32 px-6 md:px-12 bg-white">
            <div class="max-w-7xl mx-auto">
                <div class="text-center mb-20" data-aos="fade-up">
                    <h2 class="text-3xl md:text-5xl font-light uppercase tracking-widest text-black">Who We <span class="font-bold">Serve</span></h2>
                    <div class="w-10 h-1 bg-black mx-auto mt-6"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-12 lg:gap-20">
                    <!-- For Artisans -->
                    <div class="bg-[#FAFAFA] p-10 md:p-14 border border-gray-100 relative overflow-hidden group hover:bg-[#064E3B] transition-colors duration-500" data-aos="fade-right">
                        <!-- Decorative bg -->
                        <div class="absolute -right-10 -top-10 text-[150px] opacity-5 text-black group-hover:text-white transition-colors duration-500 font-serif">&amp;</div>
                        
                        <h3 class="text-2xl font-bold uppercase tracking-widest mb-8 text-black group-hover:text-white transition-colors duration-500">For Artisans</h3>
                        <div class="text-[11px] text-gray-500 uppercase tracking-widest leading-loose font-medium space-y-6 group-hover:text-gray-200 transition-colors duration-500 relative z-10">
                            <p>You have spent years &mdash; maybe generations &mdash; perfecting your craft. You deserve more than a corner of a souk or a percentage taken by a reseller.</p>
                            <p>Lamsa gives you your own shop, your own identity, and direct access to customers who are actively looking for what you make. We handle payments, logistics coordination, and visibility &mdash; so you can focus on what you do best: creating.</p>
                            <p>We're built for weavers, potters, leather workers, jewelry makers, zellige artists, woodcarvers, and every other form of Moroccan handcraft. If you make it with your hands, there's a place for you on Lamsa.</p>
                        </div>
                        <div class="mt-10 relative z-10">
                            <a href="{{ route('artisan-register') }}" class="inline-block border border-black bg-transparent text-black group-hover:border-white group-hover:text-white px-8 py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-black group-hover:hover:bg-white group-hover:hover:text-[#064E3B] transition-all">
                                Join as Artisan
                            </a>
                        </div>
                    </div>

                    <!-- For Buyers -->
                    <div class="bg-[#FAFAFA] p-10 md:p-14 border border-gray-100 relative overflow-hidden group hover:bg-black transition-colors duration-500" data-aos="fade-left">
                        <!-- Decorative bg -->
                        <div class="absolute -right-10 -bottom-10 text-[150px] opacity-5 text-black group-hover:text-white transition-colors duration-500 font-serif">@</div>
                        
                        <h3 class="text-2xl font-bold uppercase tracking-widest mb-8 text-black group-hover:text-white transition-colors duration-500">For Buyers</h3>
                        <div class="text-[11px] text-gray-500 uppercase tracking-widest leading-loose font-medium space-y-6 group-hover:text-gray-300 transition-colors duration-500 relative z-10">
                            <p>You've seen Moroccan crafts in tourist shops. You've wondered if it's real, if the price is fair, if the person who made it was paid properly.</p>
                            <p>On Lamsa, you don't have to wonder. Every product comes with the story of the artisan behind it. You shop with confidence &mdash; secure checkout, buyer protection, and the knowledge that your purchase directly supports a real craftsperson in Morocco.</p>
                            <p>Whether you're decorating a home, finding a meaningful gift, or building a personal collection of handmade objects &mdash; Lamsa is where you find the real thing.</p>
                        </div>
                        <div class="mt-10 relative z-10">
                            <a href="{{ route('home') }}#categories" class="inline-block border border-black bg-transparent text-black group-hover:border-white group-hover:text-white px-8 py-4 text-[10px] font-bold uppercase tracking-widest hover:bg-black hover:text-white group-hover:hover:bg-white group-hover:hover:text-black transition-all">
                                Explore Collections
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CLOSING / BRAND LINE -->
        <section class="min-h-screen lg:h-screen flex items-center py-24 lg:py-0 px-6 md:px-12 bg-[#064E3B] text-white text-center relative overflow-hidden">
            <!-- Animated background elements -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-10 left-10 w-32 h-32 rounded-full border border-white animate-ping" style="animation-duration: 3s;"></div>
                <div class="absolute bottom-20 right-20 w-48 h-48 rounded-full border border-white animate-ping" style="animation-duration: 4s;"></div>
            </div>

            <div class="max-w-4xl mx-auto relative z-10" data-aos="zoom-in" data-aos-duration="1000">
                <h2 class="text-5xl md:text-8xl font-light uppercase tracking-[0.2em] mb-10">
                    <span class="font-bold">Lamsa</span>
                </h2>
                <p class="text-sm md:text-lg uppercase tracking-widest font-medium mb-10 text-emerald-100">
                    &mdash; Arabic for <span class="italic text-white">a touch</span>, <span class="italic text-white">a feel</span>, <span class="italic text-white">a moment of connection.</span>
                </p>
                <div class="w-16 h-px bg-white/50 mx-auto mb-10"></div>
                <p class="text-xl md:text-3xl font-light leading-relaxed tracking-wide">
                    "That's what we're building: the moment where a buyer halfway across the world feels the craft, and an artisan in Morocco <span class="font-bold border-b-2 border-[#10B981]">feels seen</span>."
                </p>
            </div>
        </section>

        <x-footer />

        <!-- Scripts -->
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script>
            AOS.init({
                duration: 1000,
                once: true,
                offset: 50,
                easing: 'ease-out-cubic'
            });
        </script>
    </body>
</html>
