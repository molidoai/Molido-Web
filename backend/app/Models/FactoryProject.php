<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactoryProject extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'created_by',
        'name',
        'slug',
        'template',
        'status',
        'description',
        'ai_config',
        'security_config',
        'metadata',
        'default_team_id',
        'default_agent_id',
        'monthly_token_budget',
    ];

    protected function casts(): array
    {
        return [
            'ai_config' => 'array',
            'security_config' => 'array',
            'metadata' => 'array',
        ];
    }

    public const TEMPLATES = [
        'ai_saas' => 'AI SaaS',
        'ai_agent' => 'AI Agent',
        'rag_app' => 'RAG Application',
        'ai_api' => 'AI API',
        'ai_automation' => 'AI Automation',
        'internal_tool' => 'Internal AI Tool',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function defaultTeam()
    {
        return $this->belongsTo(AiTeam::class, 'default_team_id');
    }

    public function defaultAgent()
    {
        return $this->belongsTo(AiAgent::class, 'default_agent_id');
    }
}
