<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Module extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'version',
        'category',
        'description',
        'features',
        'price',
        'currency',
        'billing_type',
        'trial_days',
        'status',
        'compatibility',
        'assets',
        'is_core',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'assets' => 'array',
            'price' => 'decimal:2',
            'is_core' => 'boolean',
        ];
    }

    public function entitlements()
    {
        return $this->hasMany(Entitlement::class);
    }

    public function isFree(): bool
    {
        return $this->billing_type === 'free' || $this->price <= 0;
    }
}
