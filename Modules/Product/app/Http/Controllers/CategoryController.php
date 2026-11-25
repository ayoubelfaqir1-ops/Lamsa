<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\Product\Http\Resources\CategoryResource;
use Modules\Product\Services\ProductCatalogService;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->catalogService->getActiveCategories();

        return CategoryResource::collection($categories)
            ->response()
            ->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=60');
    }
}
