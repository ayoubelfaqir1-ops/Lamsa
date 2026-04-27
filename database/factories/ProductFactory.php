<?php

namespace Database\Factories;

use App\Models\Artisan;
use App\Models\Category;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name    = fake()->words(3, true);
        $artisan = Artisan::factory();

        return [
            'artisan_id'   => $artisan,
            'store_id'     => Store::factory(['artisan_id' => $artisan]),
            'category_id'  => Category::factory(),
            'name'         => ucfirst($name),
            'slug'         => Str::slug($name) . '-' . Str::random(4),
            'description'  => fake()->paragraph(),
            'price'        => fake()->randomFloat(2, 10, 500),
            'stock'        => fake()->numberBetween(0, 100),
            'images'       => [],
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
