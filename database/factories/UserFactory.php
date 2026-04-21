<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => Hash::make('password'),
            'remember_token'    => Str::random(10),
            'phone'             => fake()->phoneNumber(),
            'address'           => fake()->address(),
        ];
    }

    public function admin(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('admin');
        });
    }

    public function artisan(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('artisan');
        });
    }

    public function buyer(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('buyer');
        });
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
