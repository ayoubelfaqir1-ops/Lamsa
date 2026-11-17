<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HealthCheckController extends Controller
{
    /**
     * Handle the incoming health check probe.
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $isHealthy = collect($checks)->every(fn ($check) => $check['status'] === 'up');

        return response()->json([
            'status' => $isHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'environment' => config('app.env'),
            'version' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
            ],
            'services' => $checks,
        ], $isHealthy ? 200 : 503);
    }

    /**
     * Probe database connectivity and latency.
     *
     * @return array{status: string, latency_ms?: float, error?: string}
     */
    private function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'up',
                'latency_ms' => $latency,
            ];
        } catch (Exception) {
            return [
                'status' => 'down',
                'error' => 'Database connection failed',
            ];
        }
    }

    /**
     * Probe cache store read and write capabilities.
     *
     * @return array{status: string, latency_ms?: float, error?: string}
     */
    private function checkCache(): array
    {
        $start = microtime(true);
        $key = 'health_check_probe_'.Str::random(6);

        try {
            Cache::put($key, 'probe', 5);
            $val = Cache::get($key);
            Cache::forget($key);

            if ($val !== 'probe') {
                throw new Exception('Cache read/write mismatch');
            }

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'up',
                'latency_ms' => $latency,
            ];
        } catch (Exception) {
            return [
                'status' => 'down',
                'error' => 'Cache store unreachable',
            ];
        }
    }

    /**
     * Probe media storage disk read, write, and delete capabilities.
     *
     * @return array{status: string, disk: string, latency_ms?: float, error?: string}
     */
    private function checkStorage(): array
    {
        $start = microtime(true);
        $disk = (string) config('filesystems.media_disk', 'public');
        $probeFile = '.health_check_probe_'.Str::random(6);

        try {
            Storage::disk($disk)->put($probeFile, 'ok');
            $exists = Storage::disk($disk)->exists($probeFile);
            Storage::disk($disk)->delete($probeFile);

            if (! $exists) {
                throw new Exception('Storage probe verification failed');
            }

            $latency = round((microtime(true) - $start) * 1000, 2);

            return [
                'status' => 'up',
                'disk' => $disk,
                'latency_ms' => $latency,
            ];
        } catch (Exception) {
            return [
                'status' => 'down',
                'disk' => $disk,
                'error' => 'Storage disk read/write failed',
            ];
        }
    }
}
