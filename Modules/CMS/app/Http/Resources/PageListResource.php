<?php

namespace Modules\CMS\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\CMS\Models\Page;

/**
 * @mixin Page
 */
class PageListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
        ];
    }
}

