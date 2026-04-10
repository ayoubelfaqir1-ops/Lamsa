@props(['items' => []])

<div class="space-y-1">
    @foreach ($items as $item)
        <x-dashboard.sidebar-link
            :href="$item['href'] ?? '#'"
            :active="$item['active'] ?? false"
            :icon="$item['icon'] ?? null"
            :wire:navigate="$item['navigate'] ?? false"
        >
            {{ $item['label'] }}
        </x-dashboard.sidebar-link>
    @endforeach
</div>
