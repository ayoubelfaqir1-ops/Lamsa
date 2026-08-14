@php
    $product = $product ?? null;
    $method = $method ?? 'POST';
    $images = $product?->images ?? [];
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-2">
            <label for="name" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Product Name</label>
            <input
                id="name"
                name="name"
                type="text"
                value="{{ old('name', $product?->name) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#10B981] focus:outline-none"
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
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white focus:border-[#10B981] focus:outline-none"
                required
            >
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>
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
        <label for="description" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Description</label>
        <textarea
            id="description"
            name="description"
            rows="5"
            class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#10B981] focus:outline-none"
        >{{ old('description', $product?->description) }}</textarea>
        @error('description')
            <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <div class="space-y-2">
            <label for="price" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Price (MAD)</label>
            <input
                id="price"
                name="price"
                type="number"
                min="0"
                step="0.01"
                value="{{ old('price', $product?->price) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#10B981] focus:outline-none"
                required
            >
            @error('price')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-2">
            <label for="stock" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Stock</label>
            <input
                id="stock"
                name="stock"
                type="number"
                min="0"
                step="1"
                value="{{ old('stock', $product?->stock) }}"
                class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-white placeholder:text-slate-400 focus:border-[#10B981] focus:outline-none"
                required
            >
            @error('stock')
                <p class="text-xs font-bold text-rose-400">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="space-y-3">
        <label for="images" class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">Product Images</label>
        <input
            id="images"
            name="images[]"
            type="file"
            accept="image/*"
            multiple
            class="w-full border border-slate-600 bg-slate-800/70 px-4 py-3 text-sm text-slate-300 file:mr-4 file:border-0 file:bg-[#10B981] file:px-4 file:py-2 file:text-xs file:font-black file:uppercase file:tracking-[0.18em] file:text-white focus:border-[#10B981] focus:outline-none"
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
                        <img src="{{ str_starts_with($image, 'http') ? $image : Storage::url($image) }}" alt="Product image" class="h-28 w-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <label class="flex items-center gap-3 border border-slate-600 bg-slate-800/50 px-4 py-4">
        <input
            type="checkbox"
            name="is_published"
            value="1"
            class="h-4 w-4 border-slate-500 bg-slate-700 text-[#10B981] focus:ring-[#10B981]"
            @checked(old('is_published', $product?->is_published ?? false))
        >
        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-200">Visible in marketplace</span>
    </label>

    <div class="flex flex-wrap items-center gap-4">
        <button type="submit" class="bg-[#10B981] px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-white transition-all hover:bg-emerald-400">
            {{ $submitLabel }}
        </button>

        <a href="{{ route('artisan.products') }}" class="border border-slate-500 px-6 py-3 text-[10px] font-black uppercase tracking-[0.2em] text-slate-200 transition-all hover:border-slate-300 hover:text-white">
            Back to Products
        </a>
    </div>
</form>
