<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationInvite extends Model
{
    protected $fillable = [
        'organization_id',
        'invited_by',
        'email',
        'role_id',
        'token',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function isValid(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }
        return true;
    }
}
