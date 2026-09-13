<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Auth\Models\Artisan;

class StoreFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'artisan_id' => Artisan::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
