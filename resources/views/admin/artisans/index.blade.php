@php
    $sidebarItems = [
        ['label' => 'Dashboard', 'href' => route('admin.dashboard'), 'icon' => 'home'],
        ['label' => 'Artisans', 'href' => route('admin.artisans'), 'icon' => 'users', 'active' => true],
        ['label' => 'Categories', 'href' => route('admin.categories'), 'icon' => 'menu'],
    ];
@endphp

<x-dashboard.layout header-title="Artisan Registry">
    <x-slot:sidebar>
        <x-dashboard.sidebar :items="$sidebarItems" />
    </x-slot:sidebar>

    <livewire:admin.artisan-registry />
</x-dashboard.layout>
