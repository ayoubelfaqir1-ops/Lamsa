<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAuctionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('auction'));
    }

    public function rules(): array
    {
        return [
            'category_id' => ['sometimes', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'images' => ['nullable', 'array'],
            'images.*' => ['image', 'max:5120'],
            'starting_price' => ['sometimes', 'numeric', 'min:0'],
            'reserve_price' => ['nullable', 'numeric', 'min:0'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at'   => ['sometimes', 'date', 'after:starts_at'],
            'status'    => ['sometimes', Rule::in(['active', 'ended', 'cancelled'])],
            'is_published' => ['sometimes', 'boolean'],
        ];
    }
}
