<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        $store = $this->route('store');

        return $this->user()->isAdmin()
            || $this->user()->artisan?->id === $store->artisan_id;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo'        => ['nullable', 'image', 'max:2078'],
            'is_active'   => ['boolean'],
        ];
    }
}
