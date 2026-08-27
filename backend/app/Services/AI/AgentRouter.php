<?php

namespace App\Services\AI;

use App\Models\AiAgent;

/**
 * MOLIDO AI Agent Router
 * Routes requests to the appropriate agent based on intent / role.
 */
class AgentRouter
{
    /**
     * Resolve the best agent for a given intent or explicit slug.
     */
    public function resolve(?string $slug = null, ?string $intent = null, ?int $organizationId = null): ?AiAgent
    {
        // Explicit agent requested
        if ($slug) {
            $agent = AiAgent::where('slug', $slug)
                ->where(function ($q) use ($organizationId) {
                    $q->where('is_system', true)
                      ->orWhere('organization_id', $organizationId);
                })
                ->where('status', '!=', 'disabled')
                ->first();

            return $agent;
        }

        // Simple keyword-based intent routing
        if ($intent) {
            $lower = mb_strtolower($intent);
            $map = [
                'sales' => ['فروش', 'سرنخ', 'lead', 'deal', 'قیمت', 'سفارش فروش'],
                'support' => ['پشتیبانی', 'تیکت', 'مشکل', 'کمک', 'support', 'ticket'],
                'crm' => ['مشتری', 'customer', 'crm', 'مخاطب'],
                'erp' => ['موجودی', 'محصول', 'سفارش', 'inventory', 'product', 'order'],
                'finance' => ['مالی', 'هزینه', 'پرداخت', 'invoice', 'finance'],
                'marketing' => ['بازاریابی', 'کمپین', 'marketing'],
            ];

            foreach ($map as $role => $keywords) {
                foreach ($keywords as $kw) {
                    if (str_contains($lower, $kw)) {
                        return $this->getByRole($role, $organizationId);
                    }
                }
            }
        }

        // Default: general agent
        return $this->getByRole('general', $organizationId);
    }

    protected function getByRole(string $role, ?int $organizationId = null): ?AiAgent
    {
        return AiAgent::where('role', $role)
            ->where(function ($q) use ($organizationId) {
                $q->where('is_system', true)
                  ->orWhere('organization_id', $organizationId);
            })
            ->whereIn('status', ['available', 'working'])
            ->first();
    }

    /**
     * Build system prompt for an agent.
     */
    public function buildSystemPrompt(AiAgent $agent): string
    {
        $base = $agent->system_instructions
            ?? "You are {$agent->name}, a helpful AI assistant specialized in {$agent->role}.";

        $skills = is_array($agent->skills) ? implode(', ', $agent->skills) : '';
        if ($skills) {
            $base .= "\n\nYour skills: {$skills}.";
        }

        $base .= "\n\nRules:\n- Never invent business data.\n- Never claim an action was executed unless verified.\n- For sensitive operations, request human approval.\n- Answer in the user's language.";

        return $base;
    }
}
