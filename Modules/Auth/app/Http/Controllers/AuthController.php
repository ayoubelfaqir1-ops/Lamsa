<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Modules\Auth\Events\ArtisanProfileCreated;
use Modules\Auth\Events\UserRegistered;
use Modules\Auth\Http\Requests\GoogleLoginRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\UpdatePasswordRequest;
use Modules\Auth\Http\Requests\UpdateProfileRequest;
use Modules\Auth\Http\Resources\UserResource;
use Modules\Auth\Models\Artisan;
use Modules\Auth\Models\User;

class AuthController extends Controller
{
    /**
     * Register a new user (buyer or artisan).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]);

        $role = $validated['role'];
        $user->assignRole($role);

        UserRegistered::dispatch($user);

        if ($role === 'artisan') {
            $artisan = Artisan::create([
                'user_id' => $user->id,
                'bio' => $validated['bio'] ?? null,
                'city' => $validated['city'] ?? null,
                'region' => $validated['region'] ?? null,
                'craft_type' => $validated['craft_type'] ?? null,
                'status' => 'pending',
            ]);

            ArtisanProfileCreated::dispatch($artisan);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'User registered successfully. Please verify your email address before logging in.',
            'user' => new UserResource($user->load(['roles', 'artisan'])),
        ], 201);
    }

    /**
     * Authenticate user and generate access token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            $cacheKey = 'resend_cooldown:'.$user->id;

            if (! Cache::has($cacheKey)) {
                $user->sendEmailVerificationNotification();
                Cache::put($cacheKey, true, now()->addMinutes(2));

                return response()->json([
                    'message' => 'Your email address is not verified. A fresh verification link has been sent to your inbox.',
                    'email_verified' => false,
                ], 403);
            }

            return response()->json([
                'message' => 'Your email address is not verified. A verification link was recently sent, please check your inbox and spam folder.',
                'email_verified' => false,
            ], 403);
        }

        $user->tokens()->delete();
        $expiresAt = now()->addMinutes((int) config('sanctum.expiration', 10080));
        $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user->load(['roles', 'artisan'])),
        ]);
    }

    /**
     * Get the authenticated user's profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'artisan']);

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Revoke current user's access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    /**
     * Update authenticated user's profile details.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->update(array_filter([
            'name' => $validated['name'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
        ]));

        if ($user->isArtisan() && $user->artisan) {
            $user->artisan->update(array_filter([
                'bio' => $validated['bio'] ?? null,
                'city' => $validated['city'] ?? null,
                'region' => $validated['region'] ?? null,
                'craft_type' => $validated['craft_type'] ?? null,
            ]));
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user->fresh(['roles', 'artisan'])),
        ]);
    }

    /**
     * Update authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Authenticate or register user via Google OAuth ID token.
     */
    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $token = $request->validated('id_token');

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Invalid or expired Google token.',
            ], 401);
        }

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            // Prevent account takeover: reject if user was registered with email/password or another provider
            if ($user->provider_name !== 'google') {
                return response()->json([
                    'message' => 'An account with this email already exists. Please log in with your email and password.',
                ], 409);
            }

            // Verify Google provider ID consistency
            if ($user->provider_id !== $googleUser->getId()) {
                return response()->json([
                    'message' => 'Google account ID mismatch. Please contact support.',
                ], 403);
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
        $token = $user->createToken('auth_token', ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'message' => 'Google authentication successful',
            'token_type' => 'Bearer',
            'access_token' => $token,
            'user' => new UserResource($user->load(['roles', 'artisan'])),
        ]);
    }

    /**
     * Revoke all of the user's access tokens (logout from all devices).
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out from all devices successfully',
        ]);
    }

    /**
     * Verify user's email address via signed URL.
     */
    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return response()->json(['message' => 'Invalid verification link.'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully.']);
    }
}
