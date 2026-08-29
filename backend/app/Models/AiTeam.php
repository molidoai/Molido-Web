<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiTeam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'slug',
        'department',
        'description',
        'lead_agent_id',
        'routing_rules',
        'status',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'routing_rules' => 'array',
            'is_system' => 'boolean',
        ];
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function leadAgent()
    {
        return $this->belongsTo(AiAgent::class, 'lead_agent_id');
    }

    public function agents()
    {
        return $this->belongsToMany(AiAgent::class, 'ai_team_agent')
            ->withPivot(['sort_order', 'member_role'])
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    /**
     * Pick agent by simple keyword rules, else lead, else first member.
     */
    public function routeAgent(?string $message): ?AiAgent
    {
        $message = mb_strtolower($message ?? '');
        $rules = $this->routing_rules ?? [];

        foreach ($rules as $rule) {
            $keywords = $rule['keywords'] ?? [];
            $agentSlug = $rule['agent_slug'] ?? null;
            if (!$agentSlug || !is_array($keywords)) {
                continue;
            }
            foreach ($keywords as $kw) {
                if ($kw !== '' && str_contains($message, mb_strtolower((string) $kw))) {
                    $found = $this->agents->firstWhere('slug', $agentSlug)
                        ?? AiAgent::where('slug', $agentSlug)->first();
                    if ($found) {
                        return $found;
                    }
                }
            }
        }

        if ($this->lead_agent_id) {
            return $this->leadAgent ?? AiAgent::find($this->lead_agent_id);
        }

        return $this->agents->first();
    }
}
