<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsage extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'provider',
        'model',
        'agent',
        'input_tokens',
        'output_tokens',
        'estimated_cost',
        'request_type',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:6',
        ];
    }
}
