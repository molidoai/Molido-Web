<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'organization_id',
        'actor_type',
        'actor_id',
        'action',
        'entity_type',
        'entity_id',
        'metadata',
        'ip_address',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
