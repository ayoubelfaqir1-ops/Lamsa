<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Modules\Product\Http\Requests\ProductCatalogRequest;
use Modules\Product\Http\Resources\ProductCatalogResource;
use Modules\Product\Http\Resources\ProductDetailResource;
use Modules\Product\Services\ProductCatalogService;

class ProductCatalogController extends Controller
{
    public function __construct(
        private readonly ProductCatalogService $catalogService
    ) {}

    public function index(ProductCatalogRequest $request): AnonymousResourceCollection
    {
        $products = $this->catalogService->getPaginatedProducts($request->validated());

        return ProductCatalogResource::collection($products);
    }

    public function show(string $slug): ProductDetailResource
    {
        $product = $this->catalogService->getProductBySlug($slug);

        return new ProductDetailResource($product);
    }
}
