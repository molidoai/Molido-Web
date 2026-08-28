<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class HealthController extends Controller
{
    public function __invoke()
    {
        $db = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $db = 'error';
        }

        return response()->json([
            'status' => $db === 'ok' ? 'healthy' : 'degraded',
            'app' => config('app.name', 'MOLIDO'),
            'time' => now()->toIso8601String(),
            'checks' => [
                'database' => $db,
                'cache' => $this->cacheCheck(),
            ],
        ], $db === 'ok' ? 200 : 503);
    }

    protected function cacheCheck(): string
    {
        try {
            Cache::put('health_ping', 1, 10);
            return Cache::get('health_ping') === 1 ? 'ok' : 'error';
        } catch (\Throwable $e) {
            return 'error';
        }
    }
}
