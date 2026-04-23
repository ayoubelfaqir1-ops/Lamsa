<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAuctionRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Auction::class);
    }

    public function rules(): array
    {
        return [
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string'],
            'images'         => ['nullable', 'array'],
            'images.*'       => ['image', 'max:5120'],
            'starting_price' => ['required', 'numeric', 'min:0'],
            'reserve_price'  => ['nullable', 'numeric', 'min:0'],
            'starts_at'      => ['required', 'date', 'after_or_equal:now'],
            'ends_at'        => ['required', 'date', 'after:starts_at'],
            'is_published'   => ['sometimes', 'boolean'],
        ];
    }
}
