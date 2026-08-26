@props(['label', 'value', 'icon' => null, 'trend' => null, 'trendUp' => true])

<div {{ $attributes->merge(['class' => 'bg-[#1e293b] p-6 border border-slate-700 hover:border-[#064E3B] transition-all group lg:min-h-[110px] flex flex-col justify-between']) }}>
    <div class="flex justify-between items-start">
        <div class="space-y-1">
            <p class="text-xs font-black uppercase tracking-widest text-[#10B981]">{{ $label }}</p>
            <h3 class="text-4xl font-light text-white tracking-tight">{{ $value }}</h3>
        </div>
        @if($icon)
            <div class="text-slate-600 group-hover:text-slate-400 transition-colors">
                {!! $icon !!}
            </div>
        @endif
    </div>
    
    @if($trend)
        <div class="mt-4 flex items-center gap-2">
            <span class="text-xs font-bold {{ $trendUp ? 'text-emerald-500' : 'text-rose-500' }}">
                {{ $trendUp ? '+' : '-' }} {{ $trend }}
            </span>
            <span class="text-xs font-bold text-slate-600 uppercase tracking-widest">vs last month</span>
        </div>
    @endif
</div>
