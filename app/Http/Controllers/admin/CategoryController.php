<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display the admin category overview.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('products')
            ->latest()
            ->paginate(12);

        $summary = Cache::remember('admin.categories.summary', now()->addMinutes(5), function () {
            return [
                'totalCategories' => Category::count(),
                'activeCategories' => Category::where('is_active', true)->count(),
                'linkedProducts' => Category::withCount('products')->get()->sum('products_count'),
            ];
        });

        return view('admin.categories.index', compact(
            'categories',
            'summary',
        ));
    }

    /**
     * Show the category creation screen.
     */
    public function create(): View
    {
        return view('admin.categories.create');
    }

    /**
     * Store a new category.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'image' => $validated['image'] ?? null,
            'parent_id' => null,
            'is_active' => $validated['is_active'] ?? true,
        ]);
        $this->clearSummaryCache();

        return redirect()
            ->route('admin.categories.show', $category)
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the category detail page.
     */
    public function show(Category $category): View
    {
        $category->loadCount('products');

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Update the category details.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();

        $category->update([
            'name' => $validated['name'] ?? $category->name,
            'slug' => isset($validated['name'])
                ? $this->generateUniqueSlug($validated['name'], $category->id)
                : $category->slug,
            'description' => $validated['description'] ?? null,
            'image' => $validated['image'] ?? null,
            'parent_id' => null,
            'is_active' => $validated['is_active'] ?? false,
        ]);
        $this->clearSummaryCache();

        return redirect()
            ->route('admin.categories.show', $category)
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Toggle the category visibility.
     */
    public function toggleActive(Category $category): RedirectResponse
    {
        $this->authorize('update', $category);

        $category->update([
            'is_active' => ! $category->is_active,
        ]);
        $this->clearSummaryCache();

        $message = $category->is_active
            ? 'Category activated successfully.'
            : 'Category deactivated successfully.';

        return back()->with('success', $message);
    }

    /**
     * Delete the category when it is safe to do so.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            return back()->withErrors([
                'delete' => 'This category still has products linked to it and cannot be deleted.',
            ]);
        }

        $category->delete();
        $this->clearSummaryCache();

        return redirect()
            ->route('admin.categories')
            ->with('success', 'Category deleted successfully.');
    }

    private function clearSummaryCache(): void
    {
        Cache::forget('admin.categories.summary');
    }

    /**
     * Generate a unique slug for the category.
     */
    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while (
            Category::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
