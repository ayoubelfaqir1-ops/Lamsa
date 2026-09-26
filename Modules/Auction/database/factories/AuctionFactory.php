<?php

namespace Modules\Auction\Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Auction\Enums\AuctionStatus;
use Modules\Auction\Models\Auction;
use Modules\Auction\Models\Bid;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Product\Models\Category;

/**
 * @extends Factory<Auction>
 */
class AuctionFactory extends Factory
{
    protected $model = Auction::class;

    public function definition(): array
    {
        $startingPrice = fake()->randomFloat(2, 50, 300);
        $name = ucfirst(fake()->words(3, true));
        $artisan = Artisan::factory();

        return [
            'store_id' => Store::factory(['artisan_id' => $artisan]),
            'artisan_id' => $artisan,
            'category_id' => Category::factory(),
            'winner_id' => null,
            'winning_bid_id' => null,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->paragraph(),
            'images' => [
                'https://picsum.photos/seed/'.fake()->word().'/1200/900',
                'https://picsum.photos/seed/'.fake()->word().'/1200/900',
            ],
            'starting_price' => $startingPrice,
            'current_price' => $startingPrice,
            'reserve_price' => $startingPrice * 1.5,
            'status' => AuctionStatus::Active,
            'is_published' => true,
            'starts_at' => now()->subHours(fake()->numberBetween(4, 48)),
            'ends_at' => now()->addDays(fake()->numberBetween(2, 9)),
        ];
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => AuctionStatus::Scheduled,
            'starts_at' => now()->addDays(2),
            'ends_at' => now()->addDays(7),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => AuctionStatus::Active,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subMinutes(10),
        ]);
    }

    public function endingSoon(): static
    {
        return $this->state(fn () => [
            'ends_at' => now()->addHours(fake()->numberBetween(6, 20)),
        ]);
    }

    public function withBids(int $count = 3, array|Collection|null $buyers = null): static
    {
        return $this->afterCreating(function (Auction $auction) use ($count, $buyers) {
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
