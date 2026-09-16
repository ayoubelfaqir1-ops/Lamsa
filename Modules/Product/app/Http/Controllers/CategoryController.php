<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Product\Http\Resources\CategoryResource;
use Modules\Product\Services\ProductCatalogService;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService
    ) {}

    public function index(): AnonymousResourceCollection
    {
        $categories = $this->catalogService->getActiveCategories();

        return CategoryResource::collection($categories);
    }
}
