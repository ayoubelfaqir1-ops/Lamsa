<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Modules\Auth\Events\ArtisanProfileCreated;
use Modules\Auth\Events\UserRegistered;
use Modules\Auth\Models\User;
use Illuminate\Support\Facades\Notification;
use Modules\Auth\Notifications\VerifyEmailNotification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'buyer']);
        Role::firstOrCreate(['name' => 'artisan']);
    }

    public function test_buyer_can_register_via_api(): void
    {
        Event::fake();
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Test Buyer',
            'email'                 => 'buyer@example.com',
            'password'              => 'LamsaSecure#2026!Pass',
            'password_confirmation' => 'LamsaSecure#2026!Pass',
            'role'                  => 'buyer',
            'phone'                 => '+212600000000',
            'address'               => 'Casablanca, Morocco',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role', 'is_profile_complete'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'buyer@example.com',
        ]);

        Event::assertDispatched(UserRegistered::class);

        $user = User::where('email', 'buyer@example.com')->first();
        Notification::assertSentTo($user, VerifyEmailNotification::class);
    }

    public function test_artisan_can_register_via_api(): void
    {
        Event::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'Artisan Pottery',
            'email'                 => 'artisan@example.com',
            'password'              => 'LamsaSecure#2026!Pass',
            'password_confirmation' => 'LamsaSecure#2026!Pass',
            'role'                  => 'artisan',
            'city'                  => 'Fes',
            'region'                => 'Fes-Meknes',
            'craft_type'            => 'pottery',
            'bio'                   => 'Handmade ceramics from Fes.',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email', 'role', 'artisan_profile'],
            ]);

        $this->assertDatabaseHas('artisans', [
            'city'   => 'Fes',
            'status' => 'pending',
        ]);

        Event::assertDispatched(UserRegistered::class);
        Event::assertDispatched(ArtisanProfileCreated::class);
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->unverified()->create([
            'email'    => 'unverified@example.com',
            'password' => 'LamsaSecure#2026!Pass',
        ]);
        $user->assignRole('buyer');

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'unverified@example.com',
            'password' => 'LamsaSecure#2026!Pass',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'email_verified' => false,
            ]);
    }

    public function test_verified_user_can_login_via_api(): void
    {
        $user = User::factory()->create([
            'email'    => 'user@example.com',
            'password' => 'LamsaSecure#2026!Pass',
        ]);
        $user->assignRole('buyer');

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'user@example.com',
            'password' => 'LamsaSecure#2026!Pass',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'token_type',
                'access_token',
                'user',
            ]);
    }

    public function test_unverified_user_login_auto_sends_verification_with_cooldown(): void
    {
        Notification::fake();

        $user = User::factory()->unverified()->create([
            'email'    => 'unverified@example.com',
            'password' => 'LamsaSecure#2026!Pass',
        ]);

        // First login attempt triggers auto-send
        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'unverified@example.com',
            'password' => 'LamsaSecure#2026!Pass',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'email_verified' => false,
            ]);

        Notification::assertSentTo($user, VerifyEmailNotification::class);

        // Immediate second login attempt respects cooldown
        $secondResponse = $this->postJson('/api/v1/auth/login', [
            'email'    => 'unverified@example.com',
            'password' => 'LamsaSecure#2026!Pass',
        ]);

        $secondResponse->assertStatus(403)
            ->assertJson([
                'email_verified' => false,
            ]);

        // Sent count remains 1 due to cooldown
        Notification::assertSentTimes(VerifyEmailNotification::class, 1);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole('buyer');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJson([
                'user' => [
                    'email' => $user->email,
                    'role'  => 'buyer',
                ],
            ]);
    }

    public function test_authenticated_user_can_logout(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertOk()
            ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_authenticated_user_can_update_profile(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'name'  => 'Old Name',
            'phone' => '+212600000000',
        ]);
        $user->assignRole('buyer');

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/profile', [
                'name'    => 'New Name',
                'phone'   => '+212611111111',
                'address' => 'Marrakech, Morocco',
            ]);

        $response->assertOk()
            ->assertJson([
                'message' => 'Profile updated successfully',
                'user'    => [
                    'name'  => 'New Name',
                    'phone' => '+212611111111',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id'    => $user->id,
            'name'  => 'New Name',
            'phone' => '+212611111111',
        ]);
    }

    public function test_authenticated_user_can_update_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create([
            'password' => 'OldPassword#2026!Pass',
        ]);
        $user->assignRole('buyer');

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/v1/auth/password', [
                'current_password'      => 'OldPassword#2026!Pass',
                'password'              => 'NewPassword#2026!Pass',
                'password_confirmation' => 'NewPassword#2026!Pass',
            ]);

        $response->assertOk()
            ->assertJson(['message' => 'Password updated successfully']);
    }
}
