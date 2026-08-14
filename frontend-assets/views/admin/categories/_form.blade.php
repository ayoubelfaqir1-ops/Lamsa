@php
    $method = $method ?? 'POST';
    $category = $category ?? null;
@endphp

<form action="{{ $action }}" method="POST" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="space-y-2">
        <label for="name" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Category Name</label>
        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $category?->name) }}"
            class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#10B981] focus:outline-none"
            required
        >
        @error('name')
            <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="description" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#10B981] focus:outline-none"
        >{{ old('description', $category?->description) }}</textarea>
        @error('description')
            <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="image" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Image URL</label>
        <input
            id="image"
            name="image"
            type="text"
            value="{{ old('image', $category?->image) }}"
            class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#10B981] focus:outline-none"
        >
        @error('image')
            <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <label class="flex items-center gap-3 border border-slate-600 bg-slate-800/50 px-4 py-4">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            class="h-4 w-4 border-slate-500 bg-slate-700 text-[#10B981] focus:ring-[#10B981]"
            @checked(old('is_active', $category?->is_active ?? true))
        >
        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-200">Visible in marketplace navigation</span>
    </label>

    <div class="flex flex-wrap items-center gap-4">
        <button type="submit" class="bg-[#10B981] px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-emerald-400">
            {{ $submitLabel }}
        </button>

        <a href="{{ route('admin.categories') }}" class="border border-slate-500 px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-200 transition-all hover:border-slate-300 hover:text-white">
            Back to categories
        </a>
    </div>
</form>
