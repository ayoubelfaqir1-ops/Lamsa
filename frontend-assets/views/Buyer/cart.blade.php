<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Your Cart • Lamsa</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            *, *::before, *::after { border-radius: 0 !important; }
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="min-h-screen flex flex-col bg-white font-sans antialiased text-black selection:bg-[#064E3B] selection:text-white">
        <x-navbar />

        <main class="flex-grow px-6 pb-32 pt-24 md:px-16 lg:px-24">
            <div class="mx-auto max-w-[1400px]">
                <livewire:cart-page />
            </div>
        </main>

        <x-footer />
        @livewireScripts
    </body>
</html>
