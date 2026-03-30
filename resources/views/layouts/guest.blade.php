<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Lamsa') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
        </style>
    </head>
    <body class="font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white bg-white">
        <div class="min-h-screen lg:h-screen flex flex-col lg:flex-row overflow-hidden">
            
            <!-- Left Side: Brand Visual (Fixed) -->
            <div class="hidden lg:flex w-1/2 flex-col justify-between bg-black text-white p-16 relative overflow-hidden h-full shrink-0">
                <div class="relative z-10">
                    <a href="/" wire:navigate class="flex items-center gap-4 hover:opacity-80 transition-opacity">
                        <img src="{{ asset('lamsa_logo.png') }}" class="w-16 h-16 object-contain" alt="Lamsa Logo">
                        <span class="font-semibold text-3xl tracking-tighter uppercase">Lamsa</span>
                    </a>
                </div>
                
                <div class="relative z-10 mt-auto">
                    <h2 class="text-4xl lg:text-5xl xl:text-6xl font-light uppercase tracking-widest leading-tight mb-6">
                        Moroccan <br><span class="font-semibold text-[#F0FDF4] italic">Artisanal</span> <br>Heritage.
                    </h2>
                    <div class="w-16 h-1 bg-[#064E3B] mb-6"></div>
                    <p class="text-[10px] lg:text-xs uppercase tracking-widest font-medium text-gray-300 leading-relaxed max-w-sm">
                        Our core mission is to empower Moroccan master artisans, authentically showcasing their beautiful craft, and helping them market their art to the world.
                    </p>
                </div>
                
                <!-- Background Image Overlay -->
                <div class="absolute inset-0 bg-cover bg-center opacity-40 pointer-events-none" style="background-image: url('{{ asset('artisan-bg.jpg') }}');"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent pointer-events-none"></div>
            </div>

            <!-- Right Side: The Form (Scrollable) -->
            <div class="w-full lg:w-1/2 flex flex-col flex-grow px-6 py-12 sm:px-16 lg:px-24 bg-[#FAFAFA] relative lg:border-l lg:border-black overflow-y-auto">
                <div class="flex-grow flex flex-col justify-center">
                    <!-- Mobile Logo -->
                    <div class="lg:hidden flex justify-center mb-10">
                        <a href="/" wire:navigate>
                            <img src="{{ asset('lamsa_logo.png') }}" class="w-20 h-20 object-contain mix-blend-multiply" alt="Lamsa Logo">
                        </a>
                    </div>
                    
                    <div class="w-full max-w-xl mx-auto py-8">
                        {{ $slot }}
                    </div>
                </div>
            </div>
            
        </div>
    </body>
</html>
