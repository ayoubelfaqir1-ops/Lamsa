<?php

namespace Modules\Product\Database\Factories;

use Modules\Product\Enums\ProductStatus;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Auth\Models\Artisan;
use Modules\Product\Models\Category;
use Modules\Product\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);
        $artisan = Artisan::factory();

        return [
            'artisan_id' => $artisan,
            'store_id' => Store::factory(['artisan_id' => $artisan]),
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name).'-'.Str::random(4),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 500),
            'stock' => fake()->numberBetween(0, 100),
            'images' => [],
            'is_published' => true,
            'status' => ProductStatus::Active,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function inactive(): static
    {
        return $this->state(['status' => ProductStatus::Inactive]);
    }

    public function suspended(): static
    {
        return $this->state(['status' => ProductStatus::Suspended]);
    }

    public function pending(): static
    {
        return $this->state(['status' => ProductStatus::Pending]);
    }

    public function deleted(): static
    {
        return $this->state(['deleted_at' => now()]);
    }
}
