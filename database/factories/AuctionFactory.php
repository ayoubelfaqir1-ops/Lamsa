<?php

namespace Database\Factories;

use App\Enums\AuctionStatus;
use App\Models\Artisan;
use App\Models\Bid;
use App\Models\Category;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AuctionFactory extends Factory
{
    public function definition(): array
    {
        $startingPrice = fake()->randomFloat(2, 50, 300);
        $name = ucfirst(fake()->words(3, true));
        $artisan = Artisan::factory();

        return [
            'store_id'       => Store::factory(['artisan_id' => $artisan]),
            'artisan_id'     => $artisan,
            'category_id'    => Category::factory(),
            'name'           => $name,
            'slug'           => Str::slug($name) . '-' . Str::random(4),
            'description'    => fake()->paragraph(),
            'images'         => [
                fake()->imageUrl(1200, 900, 'craft', true),
                fake()->imageUrl(1200, 900, 'craft', true),
            ],
            'starting_price' => $startingPrice,
            'current_price'  => $startingPrice,
            'reserve_price'  => $startingPrice * 1.5,
            'status'         => AuctionStatus::Active,
            'is_published'   => true,
            'starts_at'      => now()->subHours(fake()->numberBetween(4, 48)),
            'ends_at'        => now()->addDays(fake()->numberBetween(2, 9)),
        ];
    }

    public function endingSoon(): static
    {
        return $this->state(fn () => [
            'ends_at' => now()->addHours(fake()->numberBetween(6, 20)),
        ]);
    }

    public function withBids(int $count = 3, array|Collection|null $buyers = null): static
    {
        return $this->afterCreating(function ($auction) use ($count, $buyers) {
            $bidBuyers = collect($buyers);
            $currentAmount = (float) $auction->starting_price;

            for ($index = 0; $index < $count; $index++) {
                $currentAmount += fake()->randomFloat(2, 15, 80);

                $buyer = $bidBuyers->isNotEmpty()
                    ? $bidBuyers[$index % $bidBuyers->count()]
                    : User::factory()->buyer()->create();

                Bid::factory()
                    ->for($auction)
                    ->for($buyer)
                    ->state([
                        'amount' => $currentAmount,
                        'created_at' => now()->subHours($count - $index),
                        'updated_at' => now()->subHours($count - $index),
                    ])
                    ->create();
            }

            $auction->update([
                'current_price' => $currentAmount,
            ]);
        });
    }
}
