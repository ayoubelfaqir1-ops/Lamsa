<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));
        $user->assignRole('buyer');
        Auth::login($user);

        $this->redirect(route('home', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-6">
    <div class="space-y-2">
        <h1 class="text-3xl md:text-4xl font-light uppercase tracking-widest text-black">
            Create <span class="font-semibold">Account</span>
        </h1>
        <div class="w-10 h-1 bg-[#064E3B]"></div>
        <p class="text-xs font-medium uppercase tracking-widest text-gray-400 pt-1">Become part of the Lamsa collection.</p>
    </div>

    <form wire:submit="register" class="space-y-4">
        <!-- Name -->
        <div class="space-y-1.5">
            <label for="name" class="block text-[10px] sm:text-xs font-bold uppercase tracking-widest text-black">Full Name</label>
            <input wire:model="name" id="name" type="text" class="w-full px-3 py-2.5 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-sm sm:text-base" required autofocus autocomplete="name" placeholder="E.g. Jane Doe">
            @error('name') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Email Address -->
        <div class="space-y-1.5">
            <label for="email" class="block text-[10px] sm:text-xs font-bold uppercase tracking-widest text-black">Email Address</label>
            <input wire:model="email" id="email" type="email" class="w-full px-3 py-2.5 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-sm sm:text-base" required autocomplete="username" placeholder="hello@lamsa.com">
            @error('email') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Password -->
            <div class="space-y-1.5">
                <label for="password" class="block text-[10px] sm:text-xs font-medium uppercase tracking-widest text-black">Password</label>
                <input wire:model="password" id="password" type="password" class="w-full px-3 py-2.5 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-sm sm:text-base" required autocomplete="new-password" placeholder="••••••••">
                @error('password') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Confirm Password -->
            <div class="space-y-1.5">
                <label for="password_confirmation" class="block text-[10px] sm:text-xs font-medium uppercase tracking-widest text-black">Confirm Password</label>
                <input wire:model="password_confirmation" id="password_confirmation" type="password" class="w-full px-3 py-2.5 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-sm sm:text-base" required autocomplete="new-password" placeholder="••••••••">
                @error('password_confirmation') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex flex-col gap-4 pt-4 mt-2 border-t border-gray-200">
            <button type="submit" class="w-full bg-black text-white px-6 py-3.5 font-semibold uppercase tracking-widest text-sm hover:bg-[#064E3B] border border-black transition-colors block text-center cursor-pointer shadow-none">
                {{ __('Secure Registration') }}
            </button>

            <div class="text-center">
                <a class="text-[10px] sm:text-xs font-medium uppercase tracking-widest text-gray-500 hover:text-black transition-colors inline-block pb-1 border-b border-transparent hover:border-black" href="{{ route('login') }}" wire:navigate>
                    {{ __('Already have an account? Log in') }}
                </a>
            </div>
        </div>
    </form>
</div>
