<?php

namespace Modules\Auction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auth\Models\User;

/**
 * @extends Factory<Bid>
 */
class BidFactory extends Factory
{
    protected $model = Bid::class;

    public function definition(): array
    {
        return [
            'auction_id' => Auction::factory(),
            'user_id' => User::factory()->buyer(),
            'amount' => fake()->randomFloat(2, 80, 900),
        ];
    }
}
