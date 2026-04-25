@props(['active' => false, 'icon' => null])

@php
$classes = ($active ?? false)
            ? 'group flex items-center gap-3 px-4 py-2.5 rounded-none bg-[#10B981] text-white font-medium text-sm transition-all shadow-md shadow-emerald-500/20'
            : 'group flex items-center gap-3 px-4 py-2.5 rounded-none text-slate-400 hover:text-white hover:bg-slate-800/50 font-medium text-sm transition-all';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <span class="w-5 h-5 flex-shrink-0 transition-transform group-hover:scale-110">
            @if (str_contains($icon, '<'))
                {!! $icon !!}
            @else
                <x-dashboard.icon :name="$icon" />
            @endif
        </span>
    @endif
    <span>{{ $slot }}</span>
</a>
