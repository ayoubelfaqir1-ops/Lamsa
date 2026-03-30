<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Artisan Studio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 border-l-4 border-[#064E3B] bg-emerald-50">
                    <h3 class="text-lg font-bold">Welcome, {{ auth()->user()->name }}</h3>
                    <p class="mt-2 text-sm">Welcome to your studio. Soon you will be able to list your artisanal products and manage your exhibitions here.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
