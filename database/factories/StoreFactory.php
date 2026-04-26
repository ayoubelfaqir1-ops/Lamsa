<?php

namespace Database\Factories;

use App\Models\Artisan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'artisan_id'  => Artisan::factory(),
            'name'        => $name,
            'slug'        => Str::slug($name) . '-' . Str::random(4),
            'description' => fake()->paragraph(),
            'is_active'   => true,
        ];
    }
}
