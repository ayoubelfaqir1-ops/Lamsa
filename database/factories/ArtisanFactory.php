<?php

namespace Database\Factories;

use App\Enums\ArtisanStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtisanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => User::factory()->artisan(),
            'bio'        => fake()->paragraph(),
            'city'       => fake()->city(),
            'region'     => fake()->state(),
            'status'     => ArtisanStatus::Active,
            'craft_type' => fake()->randomElement(['pottery', 'weaving', 'leather', 'jewelry', 'woodwork']),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => ArtisanStatus::Pending]);
    }
}
