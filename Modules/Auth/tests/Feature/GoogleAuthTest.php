<?php

namespace Modules\Auth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Modules\Auth\Events\UserRegistered;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'buyer']);
    }

    public function test_user_can_authenticate_via_google_oauth(): void
    {
        Event::fake();

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-123456');
        $abstractUser->shouldReceive('getName')->andReturn('Google User');
        $abstractUser->shouldReceive('getEmail')->andReturn('googleuser@example.com');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('userFromToken')->with('valid-google-id-token')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->postJson('/api/v1/auth/google', [
            'id_token' => 'valid-google-id-token',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email', 'role', 'avatar'],
            ])
            ->assertJson([
                'user' => [
                    'email' => 'googleuser@example.com',
                    'role' => 'buyer',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'googleuser@example.com',
            'provider_name' => 'google',
            'provider_id' => 'google-unique-id-123456',
        ]);

        Event::assertDispatched(UserRegistered::class);
    }
}
