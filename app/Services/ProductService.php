<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Artisan;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function getArtisanIndexData(Artisan $artisan): array
    {
        $products = $artisan->products()
            ->with('category')
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->withSum([
                'orderItems as total_units_sold' => function ($query) {
                    $query->whereHas('order', function ($orderQuery) {
                        $orderQuery->where('status', OrderStatus::Delivered);
                    });
                },
            ], 'quantity')
            ->latest()
            ->get();

        return [
            'products' => $products,
            'totalProducts' => $products->count(),
            'totalActive' => $products->where('status', ProductStatus::Active)->count(),
            'totalInactive' => $products->where('status', ProductStatus::Inactive)->count(),
            'totalPending' => $products->where('status', ProductStatus::Pending)->count(),
            'totalSuspended' => $products->where('status', ProductStatus::Suspended)->count(),
            'publishedProducts' => $products->where('is_published', true)->count(),
            'totalUnitsSold' => $products->sum(fn (Product $product) => (int) ($product->total_units_sold ?? 0)),
        ];
    }

    public function createProduct(Artisan $artisan, array $validated, StoreProductRequest $request): Product
    {
        return Product::create([
            'store_id' => $artisan->store->id,
            'artisan_id' => $artisan->id,
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'stock' => $validated['stock'],
            'images' => $this->storeUploadedImages($request),
            'status' => ProductStatus::Pending,
            'is_published' => $validated['is_published'] ?? false,
        ]);
    }

    public function updateProduct(Product $product, array $validated, UpdateProductRequest $request): void
    {
        $imagePaths = $product->images ?? [];

        if ($request->hasFile('images')) {
            $this->deleteStoredImages($product->images ?? []);
            $imagePaths = $this->storeUploadedImages($request);
        }

        $product->update([
            'category_id' => $validated['category_id'] ?? $product->category_id,
            'name' => $validated['name'] ?? $product->name,
            'slug' => isset($validated['name'])
                ? $this->generateUniqueSlug($validated['name'], $product->id)
                : $product->slug,
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? $product->price,
            'stock' => $validated['stock'] ?? $product->stock,
            'images' => $imagePaths,
            'is_published' => $validated['is_published'] ?? false,
        ]);
    }

    public function togglePublish(Product $product): bool
    {
        $product->update([
            'is_published' => ! $product->is_published,
        ]);

        return $product->is_published;
    }

    public function deleteProduct(Product $product): void
    {
        $this->deleteStoredImages($product->images ?? []);
        $product->delete();
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Product::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function storeUploadedImages(StoreProductRequest|UpdateProductRequest $request): array
    {
        return collect($request->file('images', []))
            ->map(fn ($image) => $image->store('products/images', 'public'))
            ->all();
    }

    private function deleteStoredImages(array $images): void
    {
        foreach ($images as $image) {
            if (is_string($image) && $image !== '') {
                Storage::disk('public')->delete($image);
            }
        }
    }
}
