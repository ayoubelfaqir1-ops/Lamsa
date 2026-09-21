<?php

namespace Modules\Auction\Http\Requests;

use App\Enums\ArtisanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Models\User;

class StoreAuctionRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $user */
        $user = $this->user();

        return $user !== null
            && $user->isArtisan()
            && $user->artisan !== null
            && $user->artisan->status === ArtisanStatus::Active;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starting_price' => ['required', 'numeric', 'min:1'],
            'reserve_price' => ['nullable', 'numeric', 'gte:starting_price'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
        ];
    }
}
