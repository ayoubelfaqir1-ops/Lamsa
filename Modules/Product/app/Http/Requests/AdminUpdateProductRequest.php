<?php

namespace Modules\Product\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', Rule::in(['pending', 'active', 'inactive', 'suspended', 'approved'])],
            'is_published' => ['sometimes', 'boolean'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
