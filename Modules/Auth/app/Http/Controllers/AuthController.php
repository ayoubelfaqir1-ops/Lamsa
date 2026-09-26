<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Http\Requests\GoogleLoginRequest;
use Modules\Auth\Http\Requests\LoginRequest;
use Modules\Auth\Http\Requests\RegisterRequest;
use Modules\Auth\Http\Requests\UpdatePasswordRequest;
use Modules\Auth\Http\Requests\UpdateProfileRequest;
use Modules\Auth\Http\Resources\UserResource;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\ProfileService;
use Modules\Auth\Services\SocialAuthService;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected ProfileService $profileService,
        protected SocialAuthService $socialAuthService
    ) {}

    /**
     * Register a new user (buyer or artisan).
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

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
        $result = $this->authService->login($request->validated());

        if ($result['status'] === 'unverified') {
            return response()->json([
                'message' => $result['message'],
                'email_verified' => false,
            ], 403);
        }

        return response()->json([
            'message' => 'Login successful',
            'token_type' => 'Bearer',
            'access_token' => $result['token'],
            'user' => new UserResource($result['user']->load(['roles', 'artisan'])),
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
        $user = $this->profileService->updateProfile(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Update authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $this->profileService->updatePassword(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }

    /**
     * Authenticate or register user via Google OAuth ID token.
     */
    public function googleLogin(GoogleLoginRequest $request): JsonResponse
    {
        $result = $this->socialAuthService->googleLogin($request->validated('id_token'));

        return match ($result['status']) {
            'invalid_token' => response()->json(['message' => $result['message']], 401),
            'conflict' => response()->json(['message' => $result['message']], 409),
            'forbidden' => response()->json(['message' => $result['message']], 403),
            'success' => response()->json([
                'message' => 'Google authentication successful',
                'token_type' => 'Bearer',
                'access_token' => $result['token'],
                'user' => new UserResource($result['user']->load(['roles', 'artisan'])),
            ]),
        };
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
        $user = \Modules\Auth\Models\User::findOrFail($id);

        $result = $this->authService->verifyEmail($user, $hash);

        if ($result['status'] === 'invalid') {
            return response()->json(['message' => $result['message']], 403);
        }

        return response()->json(['message' => $result['message']]);
    }
}
