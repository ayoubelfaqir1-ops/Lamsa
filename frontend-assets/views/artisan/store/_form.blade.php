@php
    $store = $store ?? null;
    $method = $method ?? 'POST';
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-2">
        <label for="name" class="block text-sm font-medium text-slate-300">Store Name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $store?->name) }}"
            class="block w-full rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-[#10B981] focus:ring-1 focus:ring-[#10B981] focus:outline-none transition-colors"
            required
        >
        @error('name')
            <p class="text-xs font-medium text-rose-400 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="description" class="block text-sm font-medium text-slate-300">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="block w-full rounded-lg border border-slate-700 bg-slate-900/50 px-4 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-[#10B981] focus:ring-1 focus:ring-[#10B981] focus:outline-none transition-colors"
        >{{ old('description', $store?->description) }}</textarea>
        @error('description')
            <p class="text-xs font-medium text-rose-400 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="logo" class="block text-sm font-medium text-slate-300">Store Logo</label>
        <input
            id="logo"
            name="logo"
            type="file"
            class="block w-full text-sm text-slate-400 file:mr-4 file:rounded-lg file:border-0 file:bg-slate-800 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-slate-700 focus:outline-none"
        >
        @error('logo')
            <p class="text-xs font-medium text-rose-400 mt-1">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap items-center gap-3 pt-4">
        <button type="submit" class="inline-flex justify-center rounded-lg bg-[#10B981] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#059669] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#10B981]">
            {{ $submitLabel ?? 'Create Store' }}
        </button>

        <a href="{{ $backUrl ?? route('artisan.dashboard') }}" class="inline-flex justify-center rounded-lg border border-slate-700 bg-transparent px-5 py-2.5 text-sm font-semibold text-slate-300 shadow-sm transition hover:bg-slate-800 hover:text-white">
            {{ $backLabel ?? 'Back to Dashboard' }}
        </a>
    </div>
</form>
