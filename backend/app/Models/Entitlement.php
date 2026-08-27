<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entitlement extends Model
{
    protected $fillable = [
        'organization_id',
        'module_id',
        'status',
        'source',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function isActive(): bool
    {
        if (!in_array($this->status, ['active', 'trial'])) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        if ($this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }

        return true;
    }
}
