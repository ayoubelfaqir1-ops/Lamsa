<?php

namespace Modules\Order\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;
use Modules\Order\Enums\OrderStatus;
use Modules\Order\Models\Order;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-4 months', 'now');

        return [
            'user_id' => User::factory(),
            'artisan_id' => Artisan::factory(),
            'status' => OrderStatus::Pending,
            'total_amount' => fake()->randomFloat(2, 50, 1000),
            'shipping_address' => fake()->address(),
            'payment_method' => fake()->randomElement(['cash', 'card']),
            'payment_status' => 'unpaid',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
