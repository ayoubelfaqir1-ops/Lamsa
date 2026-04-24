<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceBidRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('bid', $this->route('auction'));
    }

    public function rules(): array
    {
        $auction = $this->route('auction');
        $minBid  = $auction->minimumNextBid();

        return [
            'amount' => ['required', 'numeric', "min:{$minBid}"],
        ];
    }

    public function messages(): array
    {
        $auction = $this->route('auction');

        return [
            'amount.min' => 'Your bid must be at least '.number_format($auction->minimumNextBid(), 2).' MAD.',
        ];
    }
}
