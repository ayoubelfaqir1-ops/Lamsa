<?php

namespace Modules\Auth\Database\Factories;

use App\Enums\ArtisanStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;

class ArtisanFactory extends Factory
{
    protected $model = Artisan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->artisan(),
            'bio' => fake()->paragraph(),
            'city' => fake()->city(),
            'region' => fake()->state(),
            'status' => ArtisanStatus::Active,
            'craft_type' => fake()->randomElement(['pottery', 'weaving', 'leather', 'jewelry', 'woodwork']),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => ArtisanStatus::Pending]);
    }
}
