<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminUpdateArtisanStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:active,approved,rejected,suspended,pending'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}

