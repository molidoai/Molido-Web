<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'organization_id',
        'plan_id',
        'module_id',
        'user_id',
        'status',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'ends_at',
        'grace_days',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
            'ends_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function isActive(): bool
    {
        if (in_array($this->status, ['active', 'trialing'])) {
            if ($this->status === 'trialing' && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
                return false;
            }
            if ($this->current_period_end && $this->current_period_end->copy()->addDays($this->grace_days)->isPast()) {
                return false;
            }
            return true;
        }
        return false;
    }
}
