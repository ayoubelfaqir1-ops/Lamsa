<?php

namespace App\Http\Controllers\Buyer;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->where('is_active', true)
            ->withCount([
                'products' => function ($query) {
                    $query->where('is_published', true)
                        ->where('status', ProductStatus::Active);
                },
            ])
            ->orderBy('name')
            ->get();

        return view('categories.index', compact('categories'));
    }

    public function show(Category $category): View
    {
        abort_unless($category->is_active, 404);

        $products = $category->products()
            ->with('store')
            ->where('is_published', true)
            ->where('status', ProductStatus::Active)
            ->paginate(12);

        return view('categories.show', compact('category', 'products'));
    }
}
