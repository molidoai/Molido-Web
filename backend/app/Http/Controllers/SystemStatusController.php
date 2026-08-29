<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Aggregated status for ops — no secrets.
 */
class SystemStatusController extends Controller
{
    public function __invoke()
    {
        $db = 'ok';
        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            $db = 'error';
        }

        return response()->json([
            'app' => config('app.name'),
            'env' => config('app.env'),
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'time' => now()->toIso8601String(),
            'checks' => [
                'database' => $db,
                'storage_writable' => File::isWritable(storage_path()) ? 'ok' : 'error',
                'ai_provider_configured' => !empty(env('AI_API_KEY')) ? 'yes' : 'no',
                'payment_provider' => env('PAYMENT_PROVIDER', 'mock'),
            ],
            'modules' => [
                'core' => true,
                'crm' => true,
                'erp' => true,
                'ai_factory' => true,
                'ai_teams' => true,
                'marketplace' => true,
            ],
        ]);
    }
}
