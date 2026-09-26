<?php

namespace Modules\Auth\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Auth\Events\ArtisanProfileCreated;
use Modules\Auth\Events\UserRegistered;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;

class AuthService
{
    /**
     * Handle user registration.
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
        ]);

        $role = $data['role'];
        $user->assignRole($role);

        UserRegistered::dispatch($user);

        if ($role === 'artisan') {
            $artisan = Artisan::create([
                'user_id' => $user->id,
                'bio' => $data['bio'] ?? null,
                'city' => $data['city'] ?? null,
                'region' => $data['region'] ?? null,
                'craft_type' => $data['craft_type'] ?? null,
                'status' => 'pending',
            ]);

            ArtisanProfileCreated::dispatch($artisan);
        }

        $user->sendEmailVerificationNotification();

        return $user;
    }

    /**
     * Handle user login.
     * Returns an array with the login status.
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            return $this->handleUnverifiedEmail($user);
        }

        $user->tokens()->delete();
        $expiresAt = now()->addMinutes((int) config('sanctum.expiration', 10080));
        $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

        return [
            'status' => 'success',
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Handle logic for an unverified user attempting to log in.
     */
    protected function handleUnverifiedEmail(User $user): array
    {
        $cacheKey = 'resend_cooldown:'.$user->id;

        if (! Cache::has($cacheKey)) {
            $user->sendEmailVerificationNotification();
            Cache::put($cacheKey, true, now()->addMinutes(2));

            return [
                'status' => 'unverified',
                'message' => 'Your email address is not verified. A fresh verification link has been sent to your inbox.',
            ];
        }

        return [
            'status' => 'unverified',
            'message' => 'Your email address is not verified. A verification link was recently sent, please check your inbox and spam folder.',
        ];
    }

    /**
     * Verify a user's email.
     */
    public function verifyEmail(User $user, string $hash): array
    {
        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return ['status' => 'invalid', 'message' => 'Invalid verification link.'];
        }

        if ($user->hasVerifiedEmail()) {
            return ['status' => 'already_verified', 'message' => 'Email already verified.'];
        }

        $user->markEmailAsVerified();

        return ['status' => 'success', 'message' => 'Email verified successfully.'];
    }
}
