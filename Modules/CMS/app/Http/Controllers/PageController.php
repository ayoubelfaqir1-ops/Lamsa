<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Modules\CMS\Http\Resources\PageDetailResource;
use Modules\CMS\Http\Resources\PageListResource;
use Modules\CMS\Models\Page;

class PageController extends Controller
{
    /**
     * Display a listing of published pages (for navigation/footers).
     */
    public function index(): JsonResponse
    {
        $pages = Page::query()
            ->published()
            ->select(['id', 'title', 'slug'])
            ->latest('id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => PageListResource::collection($pages),
        ]);
    }

    /**
     * Display the specified published page by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Page::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => new PageDetailResource($page),
        ]);
    }
}

