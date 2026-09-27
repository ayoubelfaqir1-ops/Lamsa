<?php

namespace Modules\Auth\Services;

use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Events\UserRegistered;
use Modules\Auth\Models\User;

class SocialAuthService
{
    /**
     * Handle Google login/registration via ID token.
     * Returns an array with status and optional token/user.
     */
    public function googleLogin(string $token): array
    {
        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);
        } catch (\Throwable $e) {
            return [
                'status' => 'invalid_token',
                'message' => 'Invalid or expired Google token.',
            ];
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Prevent account takeover: reject if user was registered with email/password or another provider
            if ($user->provider_name !== 'google') {
                return [
                    'status' => 'conflict',
                    'message' => 'An account with this email already exists. Please log in with your email and password.',
                ];
            }

            // Verify Google provider ID consistency
            if ($user->provider_id !== $googleUser->getId()) {
                return [
                    'status' => 'forbidden',
                    'message' => 'Google account ID mismatch. Please contact support.',
                ];
            }

            // Sync avatar if user hasn't set one yet
            if (! $user->avatar && $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }
        } else {
            // Register new user via Google
            $user = User::forceCreate([
                'name' => $googleUser->getName() ?? 'User',
                'email' => $googleUser->getEmail(),
                'provider_name' => 'google',
                'provider_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
            ]);

            $user->assignRole('buyer');
            UserRegistered::dispatch($user);
        }

        $user->tokens()->delete();
        $expiresAt = now()->addMinutes((int) config('sanctum.expiration', 10080));
        $authToken = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

        return [
            'status' => 'success',
            'token' => $authToken,
            'user' => $user,
        ];
    }
}
