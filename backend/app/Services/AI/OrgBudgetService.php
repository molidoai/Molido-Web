<?php

namespace App\Services\AI;

use App\Models\AiUsage;
use App\Models\FactoryProject;
use App\Models\Organization;
use Illuminate\Support\Facades\DB;

/**
 * Lightweight token budget checks for 6GB VPS deployments.
 * No external billing engine required.
 */
class OrgBudgetService
{
    /**
     * Monthly org-level ceiling from organization.settings.ai_monthly_token_budget
     * or env AI_ORG_MONTHLY_TOKEN_BUDGET (0 = unlimited).
     */
    public function orgMonthlyLimit(?int $organizationId): ?int
    {
        if (!$organizationId) {
            return null;
        }

        $org = Organization::find($organizationId);
        $fromSettings = $org?->settings['ai_monthly_token_budget'] ?? null;
        if ($fromSettings !== null && $fromSettings !== '') {
            return (int) $fromSettings;
        }

        $env = (int) env('AI_ORG_MONTHLY_TOKEN_BUDGET', 0);

        return $env > 0 ? $env : null;
    }

    public function orgTokensUsedThisMonth(int $organizationId): int
    {
        return (int) AiUsage::where('organization_id', $organizationId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum(DB::raw('COALESCE(input_tokens,0) + COALESCE(output_tokens,0)'));
    }

    /**
     * @return array{allowed: bool, reason?: string, used?: int, limit?: int}
     */
    public function assertCanSpend(?int $organizationId, int $estimatedTokens = 0): array
    {
        if (!$organizationId) {
            return ['allowed' => true];
        }

        $limit = $this->orgMonthlyLimit($organizationId);
        if ($limit === null) {
            return ['allowed' => true];
        }

        $used = $this->orgTokensUsedThisMonth($organizationId);
        if ($used + $estimatedTokens > $limit) {
            return [
                'allowed' => false,
                'reason' => 'سقف توکن ماهانه سازمان پر شده است',
                'used' => $used,
                'limit' => $limit,
                'code' => 'budget_exceeded',
            ];
        }

        return ['allowed' => true, 'used' => $used, 'limit' => $limit];
    }

    public function projectBudgetRemaining(?int $projectId): ?array
    {
        if (!$projectId) {
            return null;
        }
        $p = FactoryProject::find($projectId);
        if (!$p || !$p->monthly_token_budget) {
            return null;
        }

        return [
            'project_id' => $p->id,
            'limit' => $p->monthly_token_budget,
        ];
    }
}
