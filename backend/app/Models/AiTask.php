<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiTask extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'agent_id',
        'user_id',
        'customer_id',
        'conversation_id',
        'title',
        'description',
        'type',
        'priority',
        'status',
        'input',
        'result',
        'error',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'result' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function agent()
    {
        return $this->belongsTo(AiAgent::class, 'agent_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'task_id');
    }
}
