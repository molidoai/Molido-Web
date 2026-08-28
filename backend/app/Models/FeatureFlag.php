<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = [
        'key',
        'enabled',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'config' => 'array',
        ];
    }

    public static function isEnabled(string $key, bool $default = false): bool
    {
        $flag = Cache::remember("feature_flag:{$key}", 60, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$flag) {
            return $default;
        }

        return (bool) $flag->enabled;
    }

    public static function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("feature_flag:{$key}");
        }
    }
}
