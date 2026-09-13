<?php

namespace Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'confirmed', Password::defaults()],
            'role'       => ['required', 'string', 'in:buyer,artisan'],
            'phone'      => ['nullable', 'string', 'max:20'],
            'address'    => ['nullable', 'string', 'max:500'],
            'bio'        => ['nullable', 'string', 'max:1000', 'prohibited_if:role,buyer'],
            'city'       => ['nullable', 'string', 'max:255', 'prohibited_if:role,buyer'],
            'region'     => ['nullable', 'string', 'max:255', 'prohibited_if:role,buyer'],
            'craft_type' => ['nullable', 'string', 'max:255', 'prohibited_if:role,buyer'],
        ];
    }
}
