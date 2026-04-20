<?php

namespace App\Http\Controllers\Buyer;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductDetailService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductDetailService $productDetailService,
    ) {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $selectedCategory = trim((string) $request->string('category'));
        $sort = trim((string) $request->string('sort', 'newest'));
        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $inStock = $request->boolean('in_stock');

        $products = Product::query()
            ->with(['category', 'store'])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->where('is_published', true)
            ->where('status', ProductStatus::Active)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nestedQuery) use ($search) {
                    $nestedQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($selectedCategory !== '', function ($query) use ($selectedCategory) {
                $query->whereHas('category', function ($categoryQuery) use ($selectedCategory) {
                    $categoryQuery->where('slug', $selectedCategory);
                });
            })
            ->when($minPrice !== null, function ($query) use ($minPrice) {
                $query->where('price', '>=', $minPrice);
            })
            ->when($maxPrice !== null, function ($query) use ($maxPrice) {
                $query->where('price', '<=', $maxPrice);
            })
            ->when($inStock, function ($query) {
                $query->where('stock', '>', 0);
            });

        $products = match ($sort) {
            'price_low' => $products->orderBy('price'),
            'price_high' => $products->orderByDesc('price'),
            'name' => $products->orderBy('name'),
            default => $products->latest(),
        };

        $products = $products
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return view('products.index', compact(
            'products',
            'categories',
            'search',
            'selectedCategory',
            'sort',
            'minPrice',
            'maxPrice',
            'inStock'
        ));
    }

    public function show(Product $product): View
    {
        return view('products.show', $this->productDetailService->build($product));
    }
}
