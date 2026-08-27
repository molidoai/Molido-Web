<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiAgent extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'role',
        'department',
        'description',
        'system_instructions',
        'skills',
        'tools',
        'permissions',
        'status',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'tools' => 'array',
            'permissions' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
