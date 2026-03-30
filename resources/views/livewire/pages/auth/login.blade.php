<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $user = Auth::user();

        if ($user->hasRole('admin')) {
            $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
        } elseif ($user->hasRole('buyer')) {
            $this->redirectIntended(default: route('home', absolute: false), navigate: true);
        } elseif ($user->hasRole('artisan')) {
            $this->redirectIntended(default: route('artisan.dashboard', absolute: false), navigate: true);
        }
    }
}; ?>

<div class="space-y-6">
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="space-y-2">
        <h1 class="text-3xl md:text-4xl font-light uppercase tracking-widest text-black">
            Welcome <span class="font-black">Back</span>
        </h1>
        <div class="w-10 h-1 bg-[#064E3B]"></div>
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 pt-1">Enter your secured credentials.</p>
    </div>

    <form wire:submit="login" class="space-y-5">
        <!-- Email Address -->
        <div class="space-y-2">
            <label for="email" class="block text-xs font-bold uppercase tracking-widest text-black">Email Address</label>
            <input wire:model="form.email" id="email" type="email" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required autofocus autocomplete="username" placeholder="hello@lamsa.com">
            @error('form.email') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Password -->
        <div class="space-y-2">
            <label for="password" class="flex justify-between items-end">
                <span class="block text-xs font-bold uppercase tracking-widest text-black">Password</span>
                @if (Route::has('password.request'))
                    <a class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-black transition-colors" href="{{ route('password.request') }}" wire:navigate>
                        Forgot?
                    </a>
                @endif
            </label>
            <input wire:model="form.password" id="password" type="password" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required autocomplete="current-password" placeholder="••••••••">
            @error('form.password') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Remember Me -->
        <div class="block mt-4 pb-2">
            <label for="remember" class="inline-flex items-center cursor-pointer group">
                <div class="relative flex items-center justify-center w-5 h-5 border border-gray-300 bg-white group-hover:border-black transition-colors">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="peer absolute opacity-0 w-full h-full cursor-pointer">
                    <svg class="w-3 h-3 text-white peer-checked:text-black transition-colors pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <span class="ms-3 text-xs font-bold uppercase tracking-widest text-gray-500 group-hover:text-black transition-colors">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-col gap-5 pt-4 border-t border-gray-200">
            <button type="submit" class="w-full bg-black text-white px-8 py-4 font-black uppercase tracking-widest text-sm hover:bg-[#064E3B] border border-black transition-colors block text-center cursor-pointer shadow-none">
                {{ __('Secure Log in') }}
            </button>

            <div class="text-center">
                <a class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black transition-colors inline-block pb-1 border-b border-transparent hover:border-black" href="{{ route('register') }}" wire:navigate>
                    {{ __('No account yet? Register now') }}
                </a>
            </div>
        </div>
    </form>
</div>
