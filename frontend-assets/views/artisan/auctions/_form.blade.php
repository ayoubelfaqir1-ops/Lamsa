@php
    $auction = $auction ?? null;
    $method = $method ?? 'POST';
    $images = $auction?->images ?? [];
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-2">
            <label for="name" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Auction Title</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $auction?->name) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-amber-400 focus:outline-none"
                required
            >
            @error('name')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="category_id" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Category</label>
            <select
                id="category_id"
                name="category_id"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white focus:border-amber-400 focus:outline-none"
                required
            >
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $auction?->category_id) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-2">
        <label for="description" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Story</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-amber-400 focus:outline-none"
        >{{ old('description', $auction?->description) }}</textarea>
        @error('description')
            <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="space-y-2">
        <label for="images" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Auction Images</label>
        <input
            id="images"
            name="images[]"
            type="file"
            accept="image/*"
            multiple
            class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-slate-300 file:mr-4 file:border-0 file:bg-amber-400 file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-[0.18em] file:text-slate-950 focus:border-amber-400 focus:outline-none"
        >
        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-500">Upload one or more images from your device. New uploads replace the current gallery.</p>
        @error('images')
            <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
        @enderror
        @error('images.*')
            <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
        @enderror

        @if ($images)
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($images as $image)
                    <div class="overflow-hidden border border-slate-700 bg-slate-900/40">
                        <img src="{{ str_starts_with($image, 'http') ? $image : Storage::url($image) }}" alt="Auction image" class="h-28 w-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-2">
            <label for="starting_price" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Starting Price (MAD)</label>
            <input
                id="starting_price"
                name="starting_price"
                type="number"
                min="0"
                step="0.01"
                value="{{ old('starting_price', $auction?->starting_price) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-amber-400 focus:outline-none"
                required
            >
            @error('starting_price')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="reserve_price" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Reserve Price (MAD)</label>
            <input
                id="reserve_price"
                name="reserve_price"
                type="number"
                min="0"
                step="0.01"
                value="{{ old('reserve_price', $auction?->reserve_price) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-amber-400 focus:outline-none"
            >
            @error('reserve_price')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-2">
            <label for="starts_at" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Starts At</label>
            <input
                id="starts_at"
                name="starts_at"
                type="datetime-local"
                value="{{ old('starts_at', optional($auction?->starts_at)->format('Y-m-d\\TH:i')) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white focus:border-amber-400 focus:outline-none"
                required
            >
            @error('starts_at')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="ends_at" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Ends At</label>
            <input
                id="ends_at"
                name="ends_at"
                type="datetime-local"
                value="{{ old('ends_at', optional($auction?->ends_at)->format('Y-m-d\\TH:i')) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white focus:border-amber-400 focus:outline-none"
                required
            >
            @error('ends_at')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <label class="flex items-center gap-3 border border-slate-600 bg-slate-800/50 px-4 py-4">
        <input
            type="checkbox"
            name="is_published"
            value="1"
            class="h-4 w-4 border-slate-500 bg-slate-700 text-amber-400 focus:ring-amber-400"
            @checked(old('is_published', $auction?->is_published ?? false))
        >
        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-200">Visible in marketplace</span>
    </label>

    <div class="flex flex-wrap items-center gap-4">
        <button type="submit" class="bg-amber-400 px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-950 transition-all hover:bg-amber-300">
            {{ $submitLabel }}
        </button>

        <a href="{{ route('artisan.auctions') }}" class="border border-slate-500 px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-200 transition-all hover:border-slate-300 hover:text-white">
            Back to Auctions
        </a>
    </div>
</form>
