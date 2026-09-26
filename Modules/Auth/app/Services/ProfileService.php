<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\User;

class ProfileService
{
    /**
     * Update the user's profile and their artisan details if applicable.
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->update(array_filter([
            'name' => $data['name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]));

        if ($user->isArtisan() && $user->artisan) {
            $user->artisan->update(array_filter([
                'bio' => $data['bio'] ?? null,
                'city' => $data['city'] ?? null,
                'region' => $data['region'] ?? null,
                'craft_type' => $data['craft_type'] ?? null,
            ]));
        }

        return $user->fresh(['roles', 'artisan']);
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(User $user, array $data): void
    {
        $user->update([
            'password' => Hash::make($data['password']),
        ]);
    }
}
