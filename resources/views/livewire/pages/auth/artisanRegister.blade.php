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
    public string $phone = '';
    public string $bio = '';
    public string $city = '';
    public string $region = '';
    public string $craft_type = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'phone'      => ['required', 'string', 'max:20'],
            'bio'        => ['nullable', 'string'],
            'city'       => ['required', 'string'],
            'region'     => ['required', 'string'],
            'craft_type' => ['required', 'string'],
        ]);

        event(new Registered($user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'phone' => $validated['phone'],
        ])));

        $user->assignRole('artisan');

        $user->artisan()->create([
            'bio' => $validated['bio'],
            'city' => $validated['city'],
            'region' => $validated['region'],
            'craft_type' => $validated['craft_type'],
        ]);

        Auth::login($user);

        $this->redirect(route('home', absolute: false), navigate: true);
    }
}; ?>

<div class="space-y-8">
    <div class="space-y-3">
        <h1 class="text-3xl md:text-5xl font-light uppercase tracking-widest text-black">
            Artisan <br><span class="font-black">Register</span>
        </h1>
        <div class="w-12 h-1 bg-[#064E3B]"></div>
        <p class="text-xs font-bold uppercase tracking-widest text-gray-400 pt-2">Join our curated gallery.</p>
    </div>

    <form wire:submit="register" class="space-y-5">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <!-- Name -->
            <div class="space-y-2">
                <label for="name" class="block text-xs font-bold uppercase tracking-widest text-black">Full Name</label>
                <input wire:model="name" id="name" type="text" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required autofocus autocomplete="name" placeholder="E.g. Jane Doe">
                @error('name') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <label for="email" class="block text-xs font-bold uppercase tracking-widest text-black">Email Address</label>
                <input wire:model="email" id="email" type="email" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required autocomplete="username" placeholder="hello@lamsa.com">
                @error('email') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
            <!-- Phone -->
            <div class="space-y-2">
                <label for="phone" class="block text-xs font-bold uppercase tracking-widest text-black">Phone Number</label>
                <input wire:model="phone" id="phone" type="text" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required placeholder="+1 234 567 8900">
                @error('phone') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Craft Type -->
            <div class="space-y-2">
                <label for="craft_type" class="block text-xs font-bold uppercase tracking-widest text-black">Craft Type</label>
                <div class="relative">
                    <select wire:model="craft_type" id="craft_type" class="w-full pl-4 pr-12 py-3 bg-white border border-gray-300 focus:border-black appearance-none outline-none font-bold text-black uppercase text-xs tracking-widest cursor-pointer rounded-none" required>
                        <option value="">Select Category</option>
                        <option value="ceramics">Ceramics</option>
                        <option value="leather">Leather Goods</option>
                        <option value="jewelry">Jewelry</option>
                        <option value="woodwork">Woodwork</option>
                        <option value="textiles">Textiles</option>
                        <option value="other">Other Art</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>
                @error('craft_type') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- City -->
            <div class="space-y-2">
                <label for="city" class="block text-xs font-bold uppercase tracking-widest text-black">City</label>
                <input wire:model="city" id="city" type="text" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required placeholder="E.g. Marrakesh">
                @error('city') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Region -->
            <div class="space-y-2">
                <label for="region" class="block text-xs font-bold uppercase tracking-widest text-black">Region / State</label>
                <input wire:model="region" id="region" type="text" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required placeholder="E.g. Marrakesh-Safi">
                @error('region') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Bio -->
        <div class="space-y-2">
            <label for="bio" class="block text-xs font-bold uppercase tracking-widest text-black">Studio Bio / Story (Optional)</label>
            <textarea wire:model="bio" id="bio" rows="3" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" placeholder="Tell us about your craft..." style="resize: none;"></textarea>
            @error('bio') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5 pb-2">
            <!-- Password -->
            <div class="space-y-2">
                <label for="password" class="block text-xs font-bold uppercase tracking-widest text-black">Password</label>
                <input wire:model="password" id="password" type="password" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required autocomplete="new-password" placeholder="••••••••">
                @error('password') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Confirm Password -->
            <div class="space-y-2">
                <label for="password_confirmation" class="block text-xs font-bold uppercase tracking-widest text-black">Confirm Password</label>
                <input wire:model="password_confirmation" id="password_confirmation" type="password" class="w-full px-4 py-3 bg-white border border-gray-300 focus:border-black focus:ring-0 outline-none transition-colors text-black font-medium text-base" required autocomplete="new-password" placeholder="••••••••">
                @error('password_confirmation') <span class="text-red-500 text-xs font-bold mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="flex flex-col gap-5 pt-6 border-t border-gray-200">
            <button type="submit" class="w-full bg-black text-white px-8 py-4 font-black uppercase tracking-widest text-sm hover:bg-[#064E3B] border border-black transition-colors block text-center cursor-pointer shadow-none">
                {{ __('Submit Request') }}
            </button>

            <div class="text-center">
                <a class="text-xs font-bold uppercase tracking-widest text-gray-500 hover:text-black transition-colors inline-block pb-1 border-b border-transparent hover:border-black" href="{{ route('login') }}" wire:navigate>
                    {{ __('Already Part of Lamsa? Log in') }}
                </a>
            </div>
        </div>
    </form>
</div>
