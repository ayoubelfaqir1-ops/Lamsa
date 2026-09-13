<?php

namespace Database\Factories;

use App\Models\Auction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\User;

class BidFactory extends Factory
{
    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory()->buyer(),
            'amount' => fake()->randomFloat(2, 80, 900),
        ];
    }
}
