<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $product->name }} | {{ config('app.name', 'Lamsa') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <style>
            *, *::before, *::after { border-radius: 0 !important; }

            .product-tab-trigger.is-active {
                border-color: #111111 !important;
                color: #111111 !important;
            }

            .product-tab-panel {
                display: none;
            }

            .product-tab-panel.is-active {
                display: block;
            }

            .related-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }

            .related-scrollbar::-webkit-scrollbar {
                display: none;
            }
        </style>
    </head>
    <body class="bg-[#fcfaf7] font-sans antialiased text-[#222222] selection:bg-[#111111] selection:text-white">
        <x-navbar />

        @php
            $productImages = collect($product->images ?? [])
                ->filter()
                ->map(fn ($image) => str_starts_with($image, 'http') ? $image : Storage::url($image))
                ->values();
            $heroImage = $productImages->first();
            $artisan = $product->artisan ?? $product->store?->artisan;
            $artisanUser = $artisan?->user;
            $store = $product->store;
            $storeName = $store?->name ?? ($artisanUser?->name ? $artisanUser->name . "'s Store" : 'Lamsa Artisan');
            $artisanImage = $store?->logo
                ? (str_starts_with($store->logo, 'http') ? $store->logo : Storage::url($store->logo))
                : null;
            $sellerInitial = strtoupper(mb_substr($artisanUser?->name ?? $storeName, 0, 1));
            $rating = round((float) ($product->reviews_avg_rating ?? 0), 1);
            $fullStars = max(0, min(5, (int) round($rating)));
            $storeRatingValue = round((float) ($storeRating ?? 0), 1);
            $storeFullStars = max(0, min(5, (int) round($storeRatingValue)));
            $reviewCount = (int) ($product->reviews_count ?? 0);
            $hasStock = $product->stock > 0;
            $defaultQuantity = $hasStock ? 1 : 0;
            $storyParts = collect(
                trim((string) $product->description) !== ''
                    ? preg_split("/\r\n|\n|\r/", trim((string) $product->description))
                    : []
            )->filter()->values();
            $storyLead = $storyParts->first() ?: 'A handmade Moroccan piece made to bring warmth, use, and character into everyday life.';
            $storyRest = $storyParts->slice(1, 2);
            $highlights = [
                ['label' => 'Made by', 'value' => $artisanUser?->name ?? 'Lamsa Artisan'],
                ['label' => 'Craft', 'value' => $artisan?->craft_type ?? 'Handmade work'],
                ['label' => 'Location', 'value' => collect([$artisan?->city, $artisan?->region])->filter()->implode(', ') ?: 'Morocco'],
                ['label' => 'Category', 'value' => $product->category?->name ?? 'Handcrafted piece'],
                ['label' => 'Availability', 'value' => $product->stock > 0 ? $product->stock . ' pieces available' : 'Made on request'],
            ];
            $makingSteps = [
                [
                    'title' => 'Material selection',
                    'text' => $product->category?->name
                        ? 'Materials are chosen within the ' . strtolower($product->category->name) . ' tradition to balance beauty with everyday durability.'
                        : 'Materials are selected for durability, touch, and the visual character they bring to the final piece.',
                ],
                [
                    'title' => 'Shaping and construction',
                    'text' => $artisan?->craft_type
                        ? 'The piece is built through ' . strtolower($artisan->craft_type) . ' techniques, then adjusted by hand for balance, proportion, and function.'
                        : 'The form is shaped slowly by hand, with repeated adjustments to get the proportions and finish right.',
                ],
                [
                    'title' => 'Finishing',
                    'text' => 'The final surface is refined by hand, which means small variations remain part of the object and make each piece individual.',
                ],
            ];
            $ratingBreakdown = collect([5, 4, 3, 2, 1])->mapWithKeys(function ($star) use ($product) {
                return [$star => $product->reviews->where('rating', $star)->count()];
            });
            $maxBreakdown = max(1, (int) $ratingBreakdown->max());
            $urgency = match (true) {
                $product->stock <= 0 => 'Made to order',
                $product->stock === 1 => 'Only 1 left',
                $product->stock <= 4 => 'Low stock',
                default => null,
            };
            $deliveryNote = match (true) {
                $product->stock <= 0 => 'This piece is made on request by the artisan.',
                $product->stock === 1 => 'Last available piece ready for checkout.',
                $product->stock <= 4 => 'Small batch availability from the artisan workshop.',
                default => 'Ready to ship from ' . (collect([$artisan?->city, $artisan?->region])->filter()->implode(', ') ?: 'the artisan studio'),
            };
            $storeActionHref = $product->category?->slug
                ? route('products.index', ['category' => $product->category->slug])
                : route('products.index');
            $sellerMetaLine = collect([
                $storeProductCount ? $storeProductCount . ' sales-ready pieces' : null,
                $artisan?->craft_type,
                collect([$artisan?->city, $artisan?->region])->filter()->implode(', '),
            ])->filter()->implode(' . ');
        @endphp

        <main class="pt-[68px]">
            <section class="bg-white">
                <div class="mx-auto max-w-7xl px-5 py-6 md:px-10 md:py-8">
                    @if (session('success'))
                        <div class="mb-5 border border-[#d6eadf] bg-[#f2faf6] px-4 py-3 text-sm text-[#064E3B]">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-5 border border-[#f1d4c9] bg-[#fff7f3] px-4 py-3 text-sm text-[#b14d16]">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mb-5 flex flex-wrap items-center gap-2 text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">
                        <a href="{{ route('products.index') }}" class="transition hover:text-[#222222]">Products</a>
                        <span>/</span>
                        <a href="{{ route('products.index', ['category' => $product->category?->slug]) }}" class="transition hover:text-[#222222]">
                            {{ $product->category?->name ?? 'Details' }}
                        </a>
                    </div>

                    <div class="grid items-start gap-8 xl:grid-cols-[0.85fr_0.75fr] xl:gap-10">
                        <div class="self-start">
                            <div class="border border-[#ece4d8] bg-[#f6f2eb]">
                                <div class="relative aspect-[1.18/1] overflow-hidden">
                                    @if ($heroImage)
                                        <img id="product-main-image" src="{{ $heroImage }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#fbfaf7_0%,#f2eee5_100%)]">
                                            <div class="flex h-28 w-28 items-center justify-center border border-[#ddd4c8] bg-white text-5xl font-light text-[#8b5a33]">
                                                {{ strtoupper(mb_substr($product->name, 0, 1)) }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($productImages->count() > 1)
                                        <button type="button" id="product-gallery-prev" class="absolute left-4 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center bg-white/95 text-[#222222] shadow-sm transition hover:bg-white">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                <path d="M12.5 4.16666L6.66667 10L12.5 15.8333" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>
                                        <button type="button" id="product-gallery-next" class="absolute right-4 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center bg-white/95 text-[#222222] shadow-sm transition hover:bg-white">
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                <path d="M7.5 4.16666L13.3333 10L7.5 15.8333" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </button>

                                        <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 items-center gap-2 bg-white/95 px-3 py-2 shadow-sm">
                                            @foreach ($productImages as $galleryImage)
                                                <button
                                                    type="button"
                                                    data-gallery-thumb="{{ $loop->index }}"
                                                    data-gallery-image="{{ $galleryImage }}"
                                                    class="gallery-thumb h-12 w-12 border {{ $loop->first ? 'border-[#222222]' : 'border-[#e7ded2]' }} bg-[#f6f2eb] transition"
                                                >
                                                    <img src="{{ $galleryImage }}" alt="{{ $product->name }} image {{ $loop->iteration }}" class="h-full w-full object-cover">
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <aside class="xl:sticky xl:top-[92px]">
                            <div class="space-y-6 bg-white p-2">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold uppercase tracking-[0.14em] text-gray-500">{{ $storeName }}</p>
                                        <h1 class="mt-2 text-[2rem] font-medium leading-tight text-[#222222]">
                                            {{ $product->name }}
                                        </h1>
                                    </div>
                                    @if ($urgency)
                                        <span class="shrink-0 text-xs font-medium text-[#b14d16]">{{ $urgency }}</span>
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2 text-[14px]">
                                    <div class="flex items-center gap-1 text-[#d0a61f]">
                                        @for ($star = 1; $star <= 5; $star++)
                                            <span class="{{ $star <= $fullStars ? 'text-[#d0a61f]' : 'text-gray-300' }}">&#9733;</span>
                                        @endfor
                                    </div>
                                    <span class="text-gray-500">
                                        {{ $reviewCount > 0 ? number_format($rating, 1) . ' (' . $reviewCount . ' reviews)' : 'New item' }}
                                    </span>
                                </div>

                                <div class="space-y-1">
                                    <p class="text-[3rem] font-semibold leading-none text-[#111111]">
                                        {{ number_format((float) $product->price, 2) }} MAD
                                    </p>
                                    <p class="text-sm text-gray-500">Local taxes included where applicable</p>
                                </div>

                                <div class="space-y-5">
                                    <div>
                                        <div class="text-[15px] leading-7 text-[#4b4b4b]">
                                            {{ \Illuminate\Support\Str::limit($storyLead, 140) }}
                                        </div>
                                    </div>

                                    <livewire:product-add-to-cart :product="$product" />
                                </div>

                                <div class="flex items-center gap-2 border-t border-[#eee5db] pt-4 text-sm text-gray-600">
                                    <svg class="h-4 w-4 text-[#222222]" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7h18M7 12h10M10 17h4"></path>
                                    </svg>
                                    <span>{{ $deliveryNote }}</span>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            <section class="border-t border-[#ece4d8] bg-[#fcfaf7]">
                <div class="mx-auto max-w-7xl px-5 py-8 md:px-10 md:py-10">
                    <div class="grid gap-8 xl:grid-cols-[1fr_340px]">
                        <div>
                            <div class="flex items-center gap-6 border-b border-[#ece4d8]">
                                <button type="button" data-tab-target="details" class="product-tab-trigger is-active border-b-2 border-transparent pb-4 text-lg font-medium text-gray-500 transition">
                                    Details
                                </button>
                                <button type="button" data-tab-target="reviews" class="product-tab-trigger border-b-2 border-transparent pb-4 text-lg font-medium text-gray-500 transition">
                                    Reviews
                                </button>
                                <button type="button" data-tab-target="making" class="product-tab-trigger border-b-2 border-transparent pb-4 text-lg font-medium text-gray-500 transition">
                                    Making process
                                </button>
                            </div>

                            <div class="pt-8">
                                <div data-tab-panel="details" class="product-tab-panel is-active space-y-6">
                                    <div class="grid gap-8 lg:grid-cols-[0.92fr_1.08fr]">
                                        <div>
                                            <p class="text-2xl font-semibold text-[#2a2927]">Highlights</p>
                                            <div class="mt-6 space-y-4">
                                                @foreach ($highlights as $highlight)
                                                    <div class="flex gap-3 text-[1.05rem] leading-7 text-[#2f2e2c]">
                                                        <span class="mt-0.5 text-[#2f2733]">&#10022;</span>
                                                        <p><span class="font-medium">{{ $highlight['label'] }}:</span> {{ $highlight['value'] }}</p>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="space-y-4 text-[1rem] leading-8 text-gray-700">
                                            <p>{{ $storyLead }}</p>
                                            @foreach ($storyRest as $paragraph)
                                                <p>{{ $paragraph }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div data-tab-panel="reviews" class="product-tab-panel space-y-8">
                                    <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="flex flex-wrap items-center gap-6">
                                            <div class="flex items-center gap-3">
                                                <span class="text-5xl font-light text-[#2a2927]">{{ number_format($rating, 1) }}</span>
                                                <div>
                                                    <div class="flex items-center gap-1 text-[#f0ab00]">
                                                        @for ($star = 1; $star <= 5; $star++)
                                                            <span>{!! $star <= $fullStars ? '&#9733;' : '&#9734;' !!}</span>
                                                        @endfor
                                                    </div>
                                                    <p class="text-sm text-gray-500">Item average</p>
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-3">
                                                <div class="flex h-14 w-14 items-center justify-center border-2 border-[#f0ab00] text-xl font-semibold text-[#2a2927]">
                                                    {{ $reviewCount }}
                                                </div>
                                                <p class="max-w-[90px] text-sm leading-5 text-gray-600">Reviews for this item</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="grid gap-6 lg:grid-cols-[1fr_220px]">
                                        <div class="space-y-6">
                                            @forelse ($product->reviews as $review)
                                                <article class="border-t border-[#ece4d8] pt-6 first:border-t-0 first:pt-0">
                                                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-2 text-[#f0ab00]">
                                                                @for ($star = 1; $star <= 5; $star++)
                                                                    <span class="text-lg">{!! $star <= $review->rating ? '&#9733;' : '&#9734;' !!}</span>
                                                                @endfor
                                                                <span class="ml-2 text-xl font-medium text-[#2a2927]">{{ $review->rating }}</span>
                                                            </div>
                                                            <p class="mt-3 max-w-4xl text-[1.02rem] leading-8 text-[#2f2e2c]">
                                                                {{ $review->comment ?: 'The buyer left a rating for this product but no written comment.' }}
                                                            </p>
                                                        </div>

                                                        <div class="shrink-0 text-right">
                                                            <p class="text-lg font-medium text-[#2a2927]">{{ $review->user?->name ?? 'Buyer' }}</p>
                                                            <p class="mt-1 text-sm text-gray-500">{{ $review->created_at?->format('M j, Y') }}</p>
                                                        </div>
                                                    </div>
                                                </article>
                                            @empty
                                                <div class="border border-dashed border-[#ddd3c7] bg-white px-8 py-12 text-center">
                                                    <p class="text-lg font-medium text-[#2a2927]">No reviews yet</p>
                                                    <p class="mt-3 text-base text-gray-600">This product is new. Reviews will appear here once buyers share feedback.</p>
                                                </div>
                                            @endforelse
                                        </div>

                                        <div class="space-y-3">
                                            @foreach ($ratingBreakdown as $star => $count)
                                                <div class="grid grid-cols-[18px_1fr_24px] items-center gap-3 text-sm text-gray-600">
                                                    <span>{{ $star }}</span>
                                                    <div class="h-2 bg-[#efe7db]">
                                                        <div class="h-2 bg-[#e0bf54]" style="width: {{ $count > 0 ? ($count / $maxBreakdown) * 100 : 0 }}%"></div>
                                                    </div>
                                                    <span>{{ $count }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <div data-tab-panel="making" class="product-tab-panel space-y-6">
                                    <div class="max-w-3xl text-[1rem] leading-8 text-gray-700">
                                        <p>This piece is made through a slower artisan process, where the maker spends more time refining the object than rushing it to completion.</p>
                                    </div>

                                    <div class="space-y-6">
                                        @foreach ($makingSteps as $step)
                                            <div class="grid gap-4 border-t border-[#ece4d8] pt-6 md:grid-cols-[220px_1fr]">
                                                <div>
                                                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">
                                                        Step {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                                    </p>
                                                    <h3 class="mt-2 text-xl font-semibold text-[#2a2927]">{{ $step['title'] }}</h3>
                                                </div>
                                                <p class="text-[1rem] leading-8 text-gray-700">{{ $step['text'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <aside class="self-start border border-[#e8dfd3] bg-white p-6 shadow-[0_10px_30px_rgba(20,14,5,0.05)]">
                            <div class="flex items-start gap-4">
                                @if ($artisanImage)
                                    <div class="h-20 w-20 overflow-hidden border border-[#e8dfd3] bg-[#f6f1ea]" style="border-radius:9999px !important;">
                                        <img src="{{ $artisanImage }}" alt="{{ $artisanUser?->name ?? $storeName }}" class="h-full w-full object-cover" style="border-radius:9999px !important;">
                                    </div>
                                @else
                                    <div class="flex h-20 w-20 items-center justify-center border border-[#e8dfd3] bg-[#f3ece2] text-3xl font-light text-[#8f5b31]" style="border-radius:9999px !important;">
                                        {{ $sellerInitial }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <h3 class="text-[1.7rem] font-medium leading-none text-[#2a2927]">{{ $artisanUser?->name ?? 'Lamsa Artisan' }}</h3>
                                    <p class="mt-2 text-[11px] font-bold uppercase tracking-[0.18em] text-gray-500">
                                        {{ $storeName }}
                                        @if ($artisan?->city || $artisan?->region)
                                            <span class="mx-1 text-gray-300">.</span>
                                            {{ collect([$artisan?->city, $artisan?->region])->filter()->implode(', ') }}
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 flex flex-wrap items-center gap-3 text-[#2a2927]">
                                <div class="flex items-center gap-1">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <span class="{{ $star <= $storeFullStars ? 'text-[#2a2927]' : 'text-gray-300' }}">&#9733;</span>
                                    @endfor
                                </div>
                                <span class="text-base font-medium">{{ $storeReviewCount > 0 ? number_format($storeRatingValue, 1) : 'New' }}</span>
                                <span class="text-gray-300">.</span>
                                <span class="text-base text-gray-600">{{ $storeReviewCount ?? 0 }} reviews</span>
                            </div>

                            <div class="mt-5 flex flex-wrap gap-x-4 gap-y-2 text-base text-gray-700">
                                <span>{{ $sellerMetaLine !== '' ? $sellerMetaLine : 'Handmade work from a Lamsa artisan' }}</span>
                            </div>

                            <p class="mt-6 text-[0.98rem] leading-7 text-gray-700">
                                {{ \Illuminate\Support\Str::limit(strip_tags(trim((string) ($store?->description ?: $artisan?->bio ?: 'This artisan works with a patient, handmade process where function and character matter equally.'))), 180) }}
                            </p>

                            <div class="mt-7 flex flex-col gap-3">
                                <a href="{{ $storeActionHref }}" class="flex h-12 items-center justify-center border border-[#2a2927] text-base font-medium text-[#2a2927] transition hover:bg-[#2a2927] hover:text-white">
                                    Visit store
                                </a>
                                <button type="button" class="flex h-12 items-center justify-center bg-[#f4f0e8] text-base font-medium text-[#2a2927] transition hover:bg-[#ece4d8]">
                                    Follow shop
                                </button>
                            </div>

                            <div class="mt-6 border-t border-[#eee5db] pt-5 text-sm text-gray-500">
                                {{ $store?->is_active ? 'Active artisan store on Lamsa' : 'Curated artisan profile on Lamsa' }}
                            </div>
                        </aside>
                    </div>
                </div>
            </section>

            @if ($relatedProducts->isNotEmpty())
                <section class="border-t border-[#ece4d8] bg-white">
                    <div class="mx-auto max-w-7xl px-5 py-10 md:px-10 md:py-12">
                        <div class="mb-8 flex flex-col items-center justify-center gap-3 text-center">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-gray-400">Curated for you</p>
                                <h2 class="mt-2 text-[2rem] font-medium text-[#222222]">You may also like</h2>
                            </div>
                            <a href="{{ route('products.index', ['category' => $product->category?->slug]) }}" class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#222222] transition hover:text-gray-600">
                                View all
                            </a>
                        </div>

                        <div class="related-scrollbar overflow-x-auto pb-2">
                            <div class="mx-auto flex min-w-max justify-center gap-6 px-1">
                            @foreach ($relatedProducts as $relatedProduct)
                                @php
                                    $relatedImage = collect($relatedProduct->images ?? [])
                                        ->filter()
                                        ->map(fn ($image) => str_starts_with($image, 'http') ? $image : Storage::url($image))
                                        ->first();
                                    $relatedRating = round((float) ($relatedProduct->reviews_avg_rating ?? 0), 1);
                                    $relatedFullStars = max(0, min(5, (int) round($relatedRating)));
                                @endphp

                                <article class="group w-[260px] shrink-0">
                                    <a href="{{ route('products.show', $relatedProduct) }}" class="block">
                                        <div class="overflow-hidden border border-[#ece4d8] bg-[#f6f2eb]">
                                            <div class="aspect-[4/3] overflow-hidden">
                                                @if ($relatedImage)
                                                    <img src="{{ $relatedImage }}" alt="{{ $relatedProduct->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                                                @else
                                                    <div class="flex h-full w-full items-center justify-center bg-[linear-gradient(135deg,#fbfaf7_0%,#f2eee5_100%)]">
                                                        <div class="flex h-20 w-20 items-center justify-center bg-white text-4xl font-light text-[#8b5a33]">
                                                            {{ strtoupper(mb_substr($relatedProduct->name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="space-y-2 pt-4">
                                            <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-gray-500">
                                                {{ $relatedProduct->store?->name ?? 'Lamsa Artisan' }}
                                            </p>
                                            <h3 class="line-clamp-2 text-[1.05rem] font-medium leading-7 text-[#222222]">
                                                {{ $relatedProduct->name }}
                                            </h3>
                                            <div class="flex items-center gap-2 text-[13px]">
                                                <div class="flex items-center gap-1 text-[#d0a61f]">
                                                    @for ($star = 1; $star <= 5; $star++)
                                                        <span class="{{ $star <= $relatedFullStars ? 'text-[#d0a61f]' : 'text-gray-300' }}">&#9733;</span>
                                                    @endfor
                                                </div>
                                                <span class="text-gray-500">
                                                    {{ $relatedProduct->reviews_count > 0 ? number_format($relatedRating, 1) : 'New' }}
                                                </span>
                                            </div>
                                            <p class="text-[1.35rem] font-semibold text-[#111111]">
                                                {{ number_format((float) $relatedProduct->price, 2) }} MAD
                                            </p>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        </main>
        <x-footer />
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tabButtons = document.querySelectorAll('[data-tab-target]');
                const tabPanels = document.querySelectorAll('[data-tab-panel]');

                tabButtons.forEach((button) => {
                    button.addEventListener('click', function () {
                        const target = button.getAttribute('data-tab-target');

                        tabButtons.forEach((btn) => btn.classList.remove('is-active'));
                        tabPanels.forEach((panel) => panel.classList.remove('is-active'));

                        button.classList.add('is-active');
                        const activePanel = document.querySelector(`[data-tab-panel="${target}"]`);
                        if (activePanel) {
                            activePanel.classList.add('is-active');
                        }
                    });
                });

                const mainImage = document.getElementById('product-main-image');
                const thumbs = Array.from(document.querySelectorAll('.gallery-thumb'));
                const prevBtn = document.getElementById('product-gallery-prev');
                const nextBtn = document.getElementById('product-gallery-next');
                let activeIndex = 0;

                function setActiveImage(index) {
                    if (!mainImage || !thumbs.length) return;
                    activeIndex = (index + thumbs.length) % thumbs.length;
                    mainImage.src = thumbs[activeIndex].getAttribute('data-gallery-image');
                    thumbs.forEach((thumb, thumbIndex) => {
                        if (thumbIndex === activeIndex) {
                            thumb.classList.remove('border-[#e7ded2]');
                            thumb.classList.add('border-[#222222]');
                        } else {
                            thumb.classList.remove('border-[#222222]');
                            thumb.classList.add('border-[#e7ded2]');
                        }
                    });
                }

                thumbs.forEach((thumb, index) => {
                    thumb.addEventListener('click', function () {
                        setActiveImage(index);
                    });
                });

                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        setActiveImage(activeIndex - 1);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        setActiveImage(activeIndex + 1);
                    });
                }

            });
        </script>
        @livewireScripts
    </body>
</html>
