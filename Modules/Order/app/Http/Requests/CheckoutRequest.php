<?php

namespace Modules\Order\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
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
            'shipping_address' => ['required', 'string', 'min:5', 'max:500'],
            'payment_method' => ['sometimes', 'string', 'in:cash,card'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
