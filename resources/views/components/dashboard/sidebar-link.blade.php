@props(['active' => false, 'icon' => null])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-4 px-6 py-4 bg-[#064E3B] text-white font-black uppercase tracking-widest text-[11px] transition-all'
            : 'flex items-center gap-4 px-6 py-4 text-gray-500 hover:text-white hover:bg-white/5 font-bold uppercase tracking-widest text-[11px] transition-all border-l-2 border-transparent hover:border-[#064E3B]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <span class="w-5 h-5">
            @if (str_contains($icon, '<'))
                {!! $icon !!}
            @else
                <x-dashboard.icon :name="$icon" />
            @endif
        </span>
    @endif
    <span>{{ $slot }}</span>
</a>
