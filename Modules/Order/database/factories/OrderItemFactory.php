<?php

namespace Modules\Order\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\Artisan;
use Modules\Order\Models\Order;
use Modules\Order\Models\OrderItem;
use Modules\Product\Models\Product;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-4 months', 'now');

        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'artisan_id' => Artisan::factory(),
            'product_name' => fake()->words(3, true),
            'quantity' => fake()->numberBetween(1, 5),
            'unit_price' => fake()->randomFloat(2, 20, 1000),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
