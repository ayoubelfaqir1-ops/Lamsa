<?php

namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $artisan = $user->artisan;
        $store = $artisan?->store;

        if (!$store) {
            return redirect()
                ->route('artisan.store.create')
                ->with('success', 'Create your store before managing products.');
        }

        return view('artisan.products.products', $this->productService->getArtisanIndexData($artisan));
    }

    /**
     * Show the product creation form.
     */
    public function create(): View|RedirectResponse
    {
        $this->authorize('create', Product::class);

        if (!Auth::user()->artisan?->store) {
            return redirect()
                ->route('artisan.store.create')
                ->with('success', 'Create your store before adding products.');
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('artisan.products.create', compact('categories'));
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $user = Auth::user();
        $artisan = $user->artisan;
        $validated = $request->validated();

        $this->productService->createProduct($artisan, $validated, $request);

        return redirect()
            ->route('artisan.products')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the product edit form.
     */
    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        $categories = Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('artisan.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the product.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $this->productService->updateProduct($product, $request->validated(), $request);

        return redirect()
            ->route('artisan.products')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Toggle product publication state.
     */
    public function togglePublish(Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        $isPublished = $this->productService->togglePublish($product);

        return back()->with('success', $isPublished
            ? 'Product published successfully.'
            : 'Product unpublished successfully.');
    }

    /**
     * Delete the product.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return redirect()
            ->route('artisan.products')
            ->with('success', 'Product deleted successfully.');
    }
}
