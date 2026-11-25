<?php

namespace Modules\CMS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Modules\CMS\Http\Resources\PageDetailResource;
use Modules\CMS\Http\Resources\PageListResource;
use Modules\CMS\Models\Page;

class PageController extends Controller
{
    /**
     * Cache TTL in seconds (24 hours).
     */
    private const CACHE_TTL = 86400;

    /**
     * Display a listing of published pages (for navigation/footers).
     */
    public function index(): JsonResponse
    {
        $pages = Cache::remember('cms_pages_list', self::CACHE_TTL, function () {
            return Page::query()
                ->published()
                ->select(['id', 'title', 'slug'])
                ->latest('id')
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => PageListResource::collection($pages),
        ])->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=60');
    }

    /**
     * Display the specified published page by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $page = Cache::remember("cms_page_{$slug}", self::CACHE_TTL, function () use ($slug) {
            return Page::query()
                ->published()
                ->where('slug', $slug)
                ->firstOrFail();
        });

        return response()->json([
            'status' => 'success',
            'data' => new PageDetailResource($page),
        ])->header('Cache-Control', 'public, max-age=3600, stale-while-revalidate=60');
    }
}
