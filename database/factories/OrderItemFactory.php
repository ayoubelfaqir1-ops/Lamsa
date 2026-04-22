<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use App\Models\Artisan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-4 months', 'now');

        return [
            'order_id'    => Order::factory(),
            'product_id'  => Product::factory(),
            'artisan_id'  => Artisan::factory(),
            'quantity'    => fake()->numberBetween(1, 5),
            'unit_price'  => fake()->randomFloat(2, 20, 1000),
            'status'      => 'pending',
            'created_at'  => $createdAt,
            'updated_at'  => $createdAt,
        ];
    }
}
