<?php

namespace Tests\Feature;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_healthy_when_all_services_are_up(): void
    {
        Storage::fake('public');

        $response = $this->getJson('/api/v1/health');

        $response->assertOk()
            ->assertJson([
                'status' => 'healthy',
                'services' => [
                    'database' => ['status' => 'up'],
                    'cache' => ['status' => 'up'],
                    'storage' => ['status' => 'up'],
                ],
            ])
            ->assertJsonStructure([
                'status',
                'timestamp',
                'environment',
                'version' => ['php', 'laravel'],
                'services' => [
                    'database' => ['status', 'latency_ms'],
                    'cache' => ['status', 'latency_ms'],
                    'storage' => ['status', 'disk', 'latency_ms'],
                ],
            ]);
    }

    public function test_health_check_is_accessible_without_authentication(): void
    {
        Storage::fake('public');

        $response = $this->getJson('/api/v1/health');

        $response->assertOk();
    }

    public function test_health_check_returns_503_when_cache_fails(): void
    {
        Storage::fake('public');

        Cache::shouldReceive('put')->andThrow(new Exception('Redis connection timeout'));

        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(503)
            ->assertJson([
                'status' => 'unhealthy',
                'services' => [
                    'cache' => [
                        'status' => 'down',
                        'error' => 'Cache store unreachable',
                    ],
                ],
            ]);
    }
}
