<x-dashboard.layout :header-title="$header_title ?? 'Overview'">
    <x-slot:sidebar>
        {{ $sidebar ?? '' }}
    </x-slot:sidebar>

    {{ $slot }}
</x-dashboard.layout>
