@php
    $useSolidNav = request()->routeIs('products.*')
        || request()->routeIs('auctions.*')
        || request()->routeIs('stores.*')
        || request()->routeIs('cart.*')
        || request()->routeIs('orders.*')
        || request()->routeIs('bids.*')
        || request()->routeIs('profile');
@endphp

<!-- Alpine.js & x-cloak Style -->
<style>
    [x-cloak] { display: none !important; }
</style>
@once
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
@endonce

<!-- NAVBAR -->
<nav id="main-nav" x-data="{ mobileMenuOpen: false, accountMenuOpen: false }" class="fixed top-0 left-0 w-full z-50 px-6 py-4 md:px-10 flex justify-between items-center transition-all duration-500 {{ $useSolidNav ? 'border-gray-100 text-black bg-white shadow-sm' : 'border-white/20 text-white bg-transparent' }}">
    
    <a href="/" class="flex items-center gap-3 group text-inherit">
        <img id="logo-img" src="{{ \Illuminate\Support\Facades\Storage::url('site/branding/lamsa_logo.png') }}" 
             class="w-8 h-8 md:w-9 md:h-9 object-contain transition-all duration-500 group-hover:scale-105 brightness-0 {{ $useSolidNav ? '' : 'invert' }}" alt="Lamsa Logo">
    </a>
    
    <div id="nav-links" class="hidden lg:flex gap-10 items-center text-[11px] font-bold uppercase tracking-[0.22em] transition-colors duration-500 absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2">
        <a href="{{ route('home') }}" class="transition-colors border-b {{ request()->routeIs('home') ? ($useSolidNav ? 'border-black pb-1 active-link text-black' : 'border-white pb-1 active-link') : ($useSolidNav ? 'border-transparent hover:border-black pb-1 hover:text-gray-600' : 'border-transparent hover:border-white pb-1') }}">Home</a>
        <a href="{{ route('auctions.index') }}" class="transition-colors border-b {{ request()->routeIs('auctions.*') ? ($useSolidNav ? 'border-black pb-1 active-link text-black' : 'border-white pb-1 active-link') : ($useSolidNav ? 'border-transparent hover:border-black pb-1 hover:text-gray-600' : 'border-transparent hover:border-white pb-1') }}">Auctions</a>
        <a href="{{ route('products.index') }}" class="transition-colors border-b {{ request()->routeIs('products.*') ? ($useSolidNav ? 'border-black pb-1 active-link text-black' : 'border-white pb-1 active-link') : ($useSolidNav ? 'border-transparent hover:border-black pb-1 hover:text-gray-600' : 'border-transparent hover:border-white pb-1') }}">Products</a>
        <a href="{{ route('mission') }}" class="transition-colors border-b {{ request()->routeIs('mission') ? ($useSolidNav ? 'border-black pb-1 active-link text-black' : 'border-white pb-1 active-link') : ($useSolidNav ? 'border-transparent hover:border-black pb-1 hover:text-gray-600' : 'border-transparent hover:border-white pb-1') }}">Our Mission</a>
    </div>

    <div class="flex items-center gap-4">
        <div id="nav-actions" class="hidden sm:flex gap-4 items-center text-[11px] font-bold uppercase tracking-[0.22em] transition-colors duration-500">
            @if (Route::has('login'))
                <a href="{{ route('cart.index') }}" class="hover:opacity-70 transition-opacity group" title="Cart">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </a>
                @auth
                    <button type="button" class="hover:opacity-70 transition-opacity group" title="Favorites" aria-label="Favorites">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                    </button>
                    @unlessrole('buyer')
                        @php
                            $dashRoute = match(true) {
                                auth()->user()->hasRole('admin') => 'admin.dashboard',
                                auth()->user()->hasRole('artisan') => 'artisan.dashboard',
                                default => 'dashboard'
                            };
                        @endphp
                        <a id="dash-btn" href="{{ route($dashRoute) }}" 
                           class="{{ $useSolidNav ? 'bg-black text-white border-black hover:bg-[#064E3B] hover:border-[#064E3B]' : 'bg-white text-black border-white hover:bg-gray-200' }} px-5 py-2 transition-colors shadow-none cursor-pointer border">Dashboard</a>
                    @endunlessrole
                    <div class="relative">
                        <button
                            type="button"
                            @click="accountMenuOpen = !accountMenuOpen"
                            @click.outside="accountMenuOpen = false"
                            id="account-btn"
                            class="flex h-10 w-10 items-center justify-center border transition-colors {{ $useSolidNav ? 'border-black text-black hover:bg-black hover:text-white' : 'border-white text-white hover:bg-white hover:text-black' }}"
                            title="Account"
                            aria-label="Open account menu"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0ZM4.5 20.25a7.5 7.5 0 0115 0"></path>
                            </svg>
                        </button>

                        <div
                            x-show="accountMenuOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            @click="accountMenuOpen = false"
                            class="absolute right-0 mt-3 w-56 border border-gray-200 bg-white p-2 text-black shadow-xl"
                        >
                            <div class="border-b border-gray-100 px-3 py-3">
                                <p class="text-[10px] font-black uppercase tracking-[0.22em] text-gray-400">Account</p>
                                <p class="mt-1 text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                            </div>

                            <div class="py-2">
                                <a href="{{ route('profile') }}" class="flex items-center justify-between px-3 py-3 text-[11px] font-bold uppercase tracking-[0.2em] text-gray-700 transition hover:bg-gray-50 hover:text-black">
                                    <span>Profile</span>
                                    <span class="text-gray-300">/</span>
                                </a>
                                @role('buyer')
                                    <a href="{{ route('orders.index') }}" class="flex items-center justify-between px-3 py-3 text-[11px] font-bold uppercase tracking-[0.2em] text-gray-700 transition hover:bg-gray-50 hover:text-black">
                                        <span>Orders</span>
                                        <span class="text-gray-300">/</span>
                                    </a>
                                    <a href="{{ route('bids.my') }}" class="flex items-center justify-between px-3 py-3 text-[11px] font-bold uppercase tracking-[0.2em] text-gray-700 transition hover:bg-gray-50 hover:text-black">
                                        <span>Bids</span>
                                        <span class="text-gray-300">/</span>
                                    </a>
                                @endrole
                            </div>
                        </div>
                    </div>
                @else
                    <a id="login-btn" href="{{ route('login') }}" 
                       class="{{ $useSolidNav ? 'border-black text-black hover:bg-black hover:text-white' : 'border-white text-white hover:bg-white hover:text-black' }} border px-6 py-2.5 transition-colors font-bold tracking-[0.22em] uppercase text-[10px]">Log in</a>
                @endauth
            @endif
        </div>

        <!-- Mobile Menu Toggle -->
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden p-2 text-inherit transition-transform active:scale-95">
            <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M4 8h16M4 16h16"></path></svg>
            <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-[-10px]"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-[-10px]"
         x-cloak
         class="fixed inset-0 top-[72px] bg-white z-40 lg:hidden flex flex-col p-10 overflow-y-auto">
        <div class="flex flex-col gap-8 text-[14px] font-bold uppercase tracking-[0.2em] text-black">
            <a @click="mobileMenuOpen = false" href="{{ route('home') }}" class="pb-2 border-b border-gray-100">Home</a>
            <a @click="mobileMenuOpen = false" href="{{ route('auctions.index') }}" class="pb-2 border-b border-gray-100">Auctions</a>
            <a @click="mobileMenuOpen = false" href="{{ route('products.index') }}" class="pb-2 border-b border-gray-100">Products</a>
            <a @click="mobileMenuOpen = false" href="{{ route('mission') }}" class="pb-2 border-b border-gray-100">Our Mission</a>
        </div>
        
        <div class="mt-auto pt-10 border-t border-gray-100 flex flex-col gap-6">
            @guest
                <a href="{{ route('login') }}" class="bg-black text-white text-center py-4 text-xs font-bold uppercase tracking-widest">Log In</a>
            @else
                <a href="{{ route('profile') }}" class="border border-black px-5 py-4 text-center text-xs font-bold uppercase tracking-widest text-black">Profile</a>
                @role('buyer')
                    <a href="{{ route('orders.index') }}" class="border border-black px-5 py-4 text-center text-xs font-bold uppercase tracking-widest text-black">Orders</a>
                    <a href="{{ route('bids.my') }}" class="bg-black text-white text-center py-4 text-xs font-bold uppercase tracking-widest">Bids</a>
                @else
                    <a href="{{ route('dashboard') }}" class="bg-black text-white text-center py-4 text-xs font-bold uppercase tracking-widest">My Dashboard</a>
                @endrole
            @endguest
        </div>
    </div>
</nav>

<script>
    function updateNav() {
        const solidNav = @json($useSolidNav);
        const nav = document.getElementById('main-nav');
        const logo = document.getElementById('logo-img');
        const dashBtn = document.getElementById('dash-btn');
        const loginBtn = document.getElementById('login-btn');
        const accountBtn = document.getElementById('account-btn');
        const navLinks = document.getElementById('nav-links');
        
        if (solidNav || window.scrollY > 50) {
            nav.classList.remove('bg-transparent', 'text-white', 'border-white/20');
            nav.classList.add('bg-white', 'text-black', 'border-gray-100', 'shadow-sm');
            logo.classList.remove('invert');
            
            if (navLinks) {
                const links = navLinks.querySelectorAll('a');
                links.forEach(link => {
                    if(link.classList.contains('active-link')) {
                        link.classList.remove('border-white');
                        link.classList.add('border-black', 'text-black');
                    } else {
                        link.classList.add('hover:text-gray-600', 'hover:border-black');
                        link.classList.remove('hover:text-gray-300', 'hover:border-white');
                    }
                });
            }
            
            if (dashBtn) {
                dashBtn.classList.remove('bg-white', 'text-black', 'border-white');
                dashBtn.classList.add('bg-black', 'text-white', 'border-black');
            }
            if (loginBtn) {
                loginBtn.classList.remove('border-white', 'text-white');
                loginBtn.classList.add('border-black', 'text-black');
            }
            if (accountBtn) {
                accountBtn.classList.remove('border-white', 'text-white');
                accountBtn.classList.add('border-black', 'text-black');
            }
        } else {
            nav.classList.add('bg-transparent', 'text-white', 'border-white/20');
            nav.classList.remove('bg-white', 'text-black', 'border-gray-100', 'shadow-sm');
            logo.classList.add('invert');
            
            if (navLinks) {
                const links = navLinks.querySelectorAll('a');
                links.forEach(link => {
                    if(link.classList.contains('active-link')) {
                        link.classList.add('border-white');
                        link.classList.remove('border-black', 'text-black');
                    } else {
                        link.classList.remove('hover:text-gray-600', 'hover:border-black');
                        link.classList.add('hover:text-gray-300', 'hover:border-white');
                    }
                });
            }
            
            if (dashBtn) {
                dashBtn.classList.add('bg-white', 'text-black', 'border-white');
                dashBtn.classList.remove('bg-black', 'text-white', 'border-black');
            }
            if (loginBtn) {
                loginBtn.classList.add('border-white', 'text-white');
                loginBtn.classList.remove('border-black', 'text-black');
            }
            if (accountBtn) {
                accountBtn.classList.add('border-white', 'text-white');
                accountBtn.classList.remove('border-black', 'text-black');
            }
        }
    }
    window.addEventListener('scroll', updateNav);
    window.addEventListener('load', updateNav);
</script>
