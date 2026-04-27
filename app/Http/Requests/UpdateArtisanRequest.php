<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateArtisanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin()
            || $this->user()->artisan?->id === $this->route('artisan')->id;
    }

    public function rules(): array
    {
        return [
            'bio'        => ['nullable', 'string'],
            'city'       => ['nullable', 'string', 'max:100'],
            'region'     => ['nullable', 'string', 'max:100'],
            'craft_type' => ['nullable', 'string', 'max:100'],
        ];
    }
}
