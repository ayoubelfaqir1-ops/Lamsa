<div class="space-y-8 lg:space-y-12">
    <!-- Header Section -->
    <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-4">
            <h1 class="text-4xl font-light uppercase tracking-[0.25em] text-white">Verification <span class="font-black italic text-[#10B981]">Circle</span></h1>
            <p class="text-xs font-black uppercase tracking-widest text-[#10B981]/60">Curation management for the Lamsa marketplace</p>
        </div>
        
        <div class="grid grid-cols-1 gap-4 border-t border-slate-800 pt-4 sm:grid-cols-2 lg:border-l lg:border-t-0 lg:gap-10 lg:pl-8 lg:pt-0">
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-2">Awaiting Review</p>
                <p class="text-3xl font-light text-white tracking-tighter">{{ $pendingCount }}<span class="text-xs text-[#10B981] font-bold ml-2">PENDING</span></p>
            </div>
            <div>
                <p class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-2">Verified Members</p>
                <p class="text-3xl font-light text-white tracking-tighter">{{ $activeCount }}<span class="text-xs text-slate-500 font-bold ml-2">TOTAL</span></p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col gap-4 border border-slate-700 bg-[#1e293b] p-3 sm:p-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="grid w-full grid-cols-1 gap-2 bg-slate-900/40 p-1 sm:w-auto sm:grid-cols-3">
            <button 
                wire:click="$set('status', 'all')"
                class="px-4 py-3 text-[9px] font-black uppercase tracking-widest transition-all sm:px-6 sm:py-2 {{ $status === 'all' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-white' }}">
                All Submissions
            </button>
            <button 
                wire:click="$set('status', 'pending')"
                class="px-4 py-3 text-[9px] font-black uppercase tracking-widest transition-all sm:px-6 sm:py-2 {{ $status === 'pending' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-white' }}">
                Pending
            </button>
            <button 
                wire:click="$set('status', 'active')"
                class="px-4 py-3 text-[9px] font-black uppercase tracking-widest transition-all sm:px-6 sm:py-2 {{ $status === 'active' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-500 hover:text-white' }}">
                Verified
            </button>
        </div>
        
        <div class="relative w-full px-4 group sm:max-w-xs">
            <input 
                wire:model.live.debounce.300ms="search"
                type="text" 
                placeholder="FIND ARTISAN..." 
                class="w-full bg-transparent border-none focus:ring-0 text-xs font-bold uppercase tracking-widest text-white placeholder-slate-700 py-3">
            <div class="absolute bottom-2 left-4 right-4 h-[1px] bg-slate-800 group-focus-within:bg-[#10B981] transition-all"></div>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="bg-emerald-500/10 border border-emerald-500/20 px-4 py-3 text-emerald-500 text-[10px] font-black uppercase tracking-widest">
            {{ session('success') }}
        </div>
    @endif

    <!-- Artisan List -->
    <div class="relative">
        <!-- Luxury Shimmer Skeleton -->
        <div wire:loading.grid class="absolute inset-0 z-10 bg-[#080b14]/10 backdrop-blur-[2px] grid grid-cols-1 gap-6">
            @for($i = 0; $i < 3; $i++)
                <div class="h-[146px] bg-[#1e293b]/80 border border-slate-700 flex items-center p-8 gap-12 overflow-hidden relative">
                    <!-- Column 1: Identity -->
                    <div class="flex items-center gap-6 min-w-[280px]">
                        <div class="w-16 h-16 shimmer opacity-20"></div>
                        <div class="space-y-3 flex-1">
                            <div class="h-4 shimmer opacity-30 w-3/4"></div>
                            <div class="h-2 shimmer opacity-10 w-1/2"></div>
                        </div>
                    </div>
                    <!-- Column 2: Communication -->
                    <div class="flex-1 border-l border-slate-800/50 pl-12 h-16 flex items-center gap-12">
                        <div class="space-y-4 flex-1">
                            <div class="h-3 shimmer opacity-10 w-2/3"></div>
                            <div class="h-3 shimmer opacity-10 w-1/2"></div>
                        </div>
                        <div class="flex-1">
                            <div class="h-4 shimmer opacity-5 w-1/2"></div>
                        </div>
                    </div>
                    <!-- Column 3: Actions -->
                    <div class="w-48 pl-12 border-l border-slate-800/50 flex flex-col gap-2">
                        <div class="h-10 shimmer opacity-20 w-full"></div>
                        <div class="h-10 shimmer opacity-5 w-full"></div>
                    </div>
                </div>
            @endfor
        </div>

        <div class="grid grid-cols-1 gap-6">
            @forelse($artisans as $artisan)
                <div wire:key="{{ $artisan->id }}" class="group flex flex-col overflow-hidden border border-slate-700 bg-[#1e293b] transition-all hover:border-slate-600 hover:bg-[#1e293b]/80 xl:flex-row xl:items-stretch">
                <!-- Status Indicator -->
                <div class="h-1.5 shrink-0 xl:h-auto xl:w-1.5 
                    {{ $artisan->status->value === 'pending' ? 'bg-amber-500' : '' }}
                    {{ $artisan->status->value === 'active' ? 'bg-[#10B981]' : '' }}
                    {{ $artisan->status->value === 'rejected' ? 'bg-rose-500' : '' }}
                "></div>

                <div class="flex flex-1 flex-col gap-6 p-5 sm:p-6 lg:p-8 xl:flex-row xl:items-start xl:justify-between xl:gap-8">
                    <!-- Profile Block -->
                    <div class="min-w-0 xl:w-[18rem]">
                        <div class="flex items-center gap-4 sm:gap-6">
                            <div class="relative shrink-0">
                                <div class="w-16 h-16 bg-slate-900 flex items-center justify-center text-base font-black uppercase text-white border border-slate-800 group-hover:border-[#10B981] group-hover:bg-[#10B981]/10 transition-all duration-500">
                                    {{ substr($artisan->user->name, 0, 2) }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 bg-[#1e293b] p-0.5 border border-slate-700">
                                    <div class="w-5 h-5 flex items-center justify-center">
                                        @if($artisan->status->value === 'active')
                                            <svg class="w-full h-full text-[#10B981]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-1-6l5-5-1.414-1.414L11 13.172l-2.086-2.086L7.5 12.5l3.5 3.5z"/></svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="min-w-0 space-y-2">
                                <h3 class="truncate text-lg font-black uppercase tracking-widest text-white leading-tight">{{ $artisan->user->name }}</h3>
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[#10B981]">{{ $artisan->craft_type }}</span>
                                    @if($artisan->store)
                                        <div class="flex min-w-0 items-center gap-1.5">
                                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">Studio /</span>
                                            <span class="truncate text-[9px] font-black text-white uppercase tracking-tighter">{{ $artisan->store->name }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Communication Block -->
                    <div class="flex-1 border-t border-slate-800 pt-6 xl:border-l xl:border-t-0 xl:pl-8 xl:pt-0">
                        <div class="grid grid-cols-1 gap-6 w-full sm:grid-cols-2 sm:gap-8">
                            <div class="space-y-3">
                                <div class="flex items-center gap-4">
                                    <div class="w-7 h-7 rounded-full bg-slate-900 flex items-center justify-center border border-slate-800">
                                        <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <div class="min-w-0 flex flex-col">
                                        <span class="text-[8px] font-black text-slate-600 uppercase tracking-widest">E-Mail Address</span>
                                        <span class="truncate text-sm font-bold text-slate-200">{{ $artisan->user->email }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <div class="w-7 h-7 rounded-full bg-slate-900 flex items-center justify-center border border-slate-800">
                                        <svg class="w-3 h-3 text-[#10B981]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="text-[8px] font-black text-slate-600 uppercase tracking-widest">Phone Number</span>
                                        <span class="text-sm font-black text-[#10B981]">{{ $artisan->user->phone ?: '+-- --- ---' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="border-t border-slate-800/50 pt-5 flex flex-col justify-center sm:border-l sm:border-t-0 sm:pl-8 sm:pt-0">
                                <span class="text-[8px] font-black text-slate-600 uppercase tracking-widest mb-2">Regional Origin</span>
                                <div class="flex items-center gap-3">
                                    <div class="w-1 h-1 rounded-full bg-[#10B981]"></div>
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-widest">{{ $artisan->city }}, {{ $artisan->region }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Status & Action Block -->
                    <div class="border-t border-slate-800 pt-6 xl:w-[14rem] xl:shrink-0 xl:border-l xl:border-t-0 xl:pl-8 xl:pt-0">
                        @if($artisan->status->value === 'pending')
                            <div class="space-y-3">
                                <div class="inline-flex items-center gap-2 border border-amber-500/20 bg-amber-500/5 px-4 py-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-amber-300">Pending Review</span>
                                </div>
                                <button 
                                    wire:click="updateStatus({{ $artisan->id }}, 'active')"
                                    wire:loading.attr="disabled"
                                    class="w-full bg-[#10B981] text-white px-6 py-3 text-[9px] font-black uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/10 hover:bg-emerald-400 transition-all duration-300 disabled:opacity-50">
                                    Approve Artisan
                                </button>
                                <button 
                                    wire:click="updateStatus({{ $artisan->id }}, 'rejected')"
                                    wire:loading.attr="disabled"
                                    class="w-full border border-rose-500/30 bg-rose-500/5 text-rose-300 px-6 py-3 text-[9px] font-black uppercase tracking-[0.2em] hover:bg-rose-500/10 hover:border-rose-500/40 hover:text-white transition-all duration-300 disabled:opacity-50">
                                    Reject Application
                                </button>
                            </div>
                        @elseif($artisan->status->value === 'active')
                            <div class="space-y-3">
                                <div class="inline-flex items-center gap-2 border border-emerald-500/20 bg-emerald-500/5 px-4 py-2">
                                    <span class="w-2 h-2 rounded-full bg-[#10B981]"></span>
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-[#10B981]">Verified</span>
                                </div>
                                <button 
                                    wire:click="updateStatus({{ $artisan->id }}, 'suspended')"
                                    class="w-full border border-amber-500/30 bg-amber-500/5 px-6 py-3 text-[9px] font-black uppercase tracking-[0.2em] text-amber-300 hover:bg-amber-500/10 hover:border-amber-400 hover:text-white transition-all duration-300">
                                    Suspend Artisan
                                </button>
                            </div>
                        @elseif($artisan->status->value === 'suspended')
                            <div class="space-y-3">
                                <div class="inline-flex items-center gap-2 border border-amber-500/20 bg-amber-500/5 px-4 py-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-amber-300">Suspended</span>
                                </div>
                                <button 
                                    wire:click="updateStatus({{ $artisan->id }}, 'active')"
                                    class="w-full border border-emerald-500/30 bg-emerald-500/5 px-6 py-3 text-[9px] font-black uppercase tracking-[0.2em] text-emerald-300 hover:bg-emerald-500/10 hover:border-emerald-400 hover:text-white transition-all duration-300">
                                    Reactivate Artisan
                                </button>
                            </div>
                        @else
                            <div class="space-y-3">
                                <div class="inline-flex items-center gap-2 border border-rose-500/20 bg-rose-500/5 px-4 py-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                    <span class="text-[9px] font-black uppercase tracking-[0.2em] text-rose-300">{{ strtoupper($artisan->status->value) }}</span>
                                </div>
                                <button 
                                    wire:click="updateStatus({{ $artisan->id }}, 'active')"
                                    class="w-full border border-emerald-500/30 bg-emerald-500/5 px-6 py-3 text-[9px] font-black uppercase tracking-[0.2em] text-emerald-300 hover:bg-emerald-500/10 hover:border-emerald-400 hover:text-white transition-all duration-300">
                                    Approve Artisan
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-40 text-center bg-slate-900/20 border border-dashed border-slate-800">
                <div class="inline-block p-8 rounded-full bg-slate-900 mb-8 shadow-xl border border-slate-800">
                    <svg class="w-14 h-14 text-slate-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="square" stroke-linejoin="miter" stroke-width="1.5" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m8-10a4 4 0 11-8 0 4 4 0 018 0zm6 3l2 2 4-4"></path></svg>
                </div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-600 italic">No artisans found matching your criteria.</p>
            </div>
        @endforelse
    </div>
</div>

    <!-- Pagination -->
    <div class="mt-12">
        {{ $artisans->links(data: ['scrollTo' => false]) }}
    </div>
</div>
