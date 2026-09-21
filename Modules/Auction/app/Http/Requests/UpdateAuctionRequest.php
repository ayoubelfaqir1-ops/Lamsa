<?php

namespace Modules\Auction\Http\Requests;

use App\Enums\ArtisanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Modules\Auth\Models\User;

class UpdateAuctionRequest extends FormRequest
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
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'starting_price' => ['sometimes', 'numeric', 'min:1'],
            'reserve_price' => ['nullable', 'numeric'],
            'starts_at' => ['sometimes', 'date'],
            'ends_at' => ['sometimes', 'date'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
        ];
    }
}
