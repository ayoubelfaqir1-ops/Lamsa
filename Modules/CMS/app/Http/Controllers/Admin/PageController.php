<?php

namespace Modules\CMS\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\CMS\Http\Requests\Admin\StorePageRequest;
use Modules\CMS\Http\Requests\Admin\UpdatePageRequest;
use Modules\CMS\Http\Resources\Admin\PageResource;
use Modules\CMS\Models\Page;

class PageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $pages = Page::query()
            ->latest('id')
            ->paginate((int) $request->query('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => PageResource::collection($pages),
            'meta' => [
                'current_page' => $pages->currentPage(),
                'last_page' => $pages->lastPage(),
                'per_page' => $pages->perPage(),
                'total' => $pages->total(),
            ],
        ]);
    }

    public function store(StorePageRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title']);
        }

        $page = Page::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Page created successfully',
            'data' => new PageResource($page),
        ], 201);
    }

    public function show(Page $page): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => new PageResource($page),
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): JsonResponse
    {
        $validated = $request->validated();

        if (array_key_exists('slug', $validated) && empty($validated['slug']) && ! empty($validated['title'])) {
            $validated['slug'] = $this->generateUniqueSlug($validated['title'], $page->id);
        }

        $page->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Page updated successfully',
            'data' => new PageResource($page->fresh()),
        ]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Page deleted successfully',
        ]);
    }

    private function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (Page::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}

