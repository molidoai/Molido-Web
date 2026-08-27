<?php

namespace App\Http\Controllers\Subscription;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = Plan::where('is_active', true)->with('module:id,name,slug')->get();
        return response()->json(['plans' => $plans]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $subs = Subscription::where('organization_id', $user->organization_id)
            ->with(['plan', 'module:id,name,slug'])
            ->latest()
            ->get();

        return response()->json(['subscriptions' => $subs]);
    }

    public function subscribe(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'plan_slug' => 'required|string',
        ]);

        $plan = Plan::where('slug', $validated['plan_slug'])
            ->where('is_active', true)
            ->firstOrFail();

        $service = app(SubscriptionService::class);
        $subscription = $service->subscribe(
            $user->organization_id,
            $user->id,
            $plan
        );

        return response()->json([
            'message' => 'اشتراک ایجاد / فعال شد',
            'subscription' => $subscription->load(['plan', 'module']),
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();

        $subscription = Subscription::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $immediately = $request->boolean('immediately', false);

        $service = app(SubscriptionService::class);
        $subscription = $service->cancel($subscription, $immediately);

        return response()->json([
            'message' => 'اشتراک لغو شد',
            'subscription' => $subscription,
        ]);
    }
}
