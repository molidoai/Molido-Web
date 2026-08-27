<?php

namespace App\Services;

use App\Models\Entitlement;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Start subscription (with optional trial).
     */
    public function subscribe(int $organizationId, int $userId, Plan $plan): Subscription
    {
        return DB::transaction(function () use ($organizationId, $userId, $plan) {
            $existing = Subscription::where('organization_id', $organizationId)
                ->where('plan_id', $plan->id)
                ->first();

            if ($existing && $existing->isActive()) {
                return $existing;
            }

            $now = now();
            $trialDays = (int) $plan->trial_days;

            if ($trialDays > 0) {
                $status = 'trialing';
                $trialEnds = $now->copy()->addDays($trialDays);
                $periodEnd = $trialEnds;
            } else {
                $status = 'active';
                $trialEnds = null;
                $periodEnd = $plan->interval === 'yearly'
                    ? $now->copy()->addYear()
                    : $now->copy()->addMonth();
            }

            $subscription = Subscription::updateOrCreate(
                [
                    'organization_id' => $organizationId,
                    'plan_id' => $plan->id,
                ],
                [
                    'module_id' => $plan->module_id,
                    'user_id' => $userId,
                    'status' => $status,
                    'trial_ends_at' => $trialEnds,
                    'current_period_start' => $now,
                    'current_period_end' => $periodEnd,
                    'cancelled_at' => null,
                    'ends_at' => null,
                    'grace_days' => 3,
                ]
            );

            // Sync entitlement if plan is linked to a module
            if ($plan->module_id) {
                Entitlement::updateOrCreate(
                    [
                        'organization_id' => $organizationId,
                        'module_id' => $plan->module_id,
                    ],
                    [
                        'status' => $status === 'trialing' ? 'trial' : 'active',
                        'source' => 'subscription',
                        'starts_at' => $now,
                        'trial_ends_at' => $trialEnds,
                        'ends_at' => $periodEnd,
                    ]
                );
            }

            return $subscription;
        });
    }

    public function cancel(Subscription $subscription, bool $immediately = false): Subscription
    {
        if ($immediately) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'ends_at' => now(),
            ]);

            if ($subscription->module_id) {
                Entitlement::where('organization_id', $subscription->organization_id)
                    ->where('module_id', $subscription->module_id)
                    ->update(['status' => 'cancelled', 'ends_at' => now()]);
            }
        } else {
            // Cancel at period end
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'ends_at' => $subscription->current_period_end,
            ]);
        }

        return $subscription->fresh();
    }

    /**
     * Check and expire trials / periods (to be called by scheduler).
     */
    public function processExpirations(): int
    {
        $count = 0;

        // Expire trials
        $trialing = Subscription::where('status', 'trialing')
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->get();

        foreach ($trialing as $sub) {
            $sub->update(['status' => 'expired']);
            if ($sub->module_id) {
                Entitlement::where('organization_id', $sub->organization_id)
                    ->where('module_id', $sub->module_id)
                    ->update(['status' => 'expired']);
            }
            $count++;
        }

        // Expire active periods (after grace)
        $active = Subscription::where('status', 'active')
            ->whereNotNull('current_period_end')
            ->get();

        foreach ($active as $sub) {
            $graceEnd = $sub->current_period_end->copy()->addDays($sub->grace_days);
            if ($graceEnd->isPast()) {
                $sub->update(['status' => 'expired']);
                if ($sub->module_id) {
                    Entitlement::where('organization_id', $sub->organization_id)
                        ->where('module_id', $sub->module_id)
                        ->update(['status' => 'expired']);
                }
                $count++;
            }
        }

        return $count;
    }
}
