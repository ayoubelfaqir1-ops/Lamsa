<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Artisan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $createdAt = fake()->dateTimeBetween('-4 months', 'now');

        return [
            'user_id'          => User::factory(),
            'artisan_id'       => Artisan::factory(),
            'status'           => OrderStatus::Pending,
            'total_amount'     => fake()->randomFloat(2, 50, 1000),
            'shipping_address' => fake()->address(),
            'payment_method'   => fake()->randomElement(['cash', 'card']),
            'payment_status'   => 'unpaid',
            'created_at'       => $createdAt,
            'updated_at'       => $createdAt,
        ];
    }
}
