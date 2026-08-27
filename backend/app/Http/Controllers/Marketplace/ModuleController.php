<?php

namespace App\Http\Controllers\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Entitlement;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * List marketplace modules
     */
    public function index(Request $request)
    {
        $query = Module::where('status', 'active')->orderBy('sort_order')->orderBy('name');

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        $modules = $query->get();

        // Attach entitlement status for current org if authenticated
        $user = $request->user();
        if ($user && $user->organization_id) {
            $entitlements = Entitlement::where('organization_id', $user->organization_id)
                ->get()
                ->keyBy('module_id');

            $modules->transform(function ($module) use ($entitlements) {
                $ent = $entitlements->get($module->id);
                $module->entitlement_status = $ent ? $ent->status : null;
                $module->is_entitled = $ent ? $ent->isActive() : false;
                return $module;
            });
        }

        return response()->json(['modules' => $modules]);
    }

    public function show(Request $request, $slug)
    {
        $module = Module::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $user = $request->user();
        if ($user && $user->organization_id) {
            $ent = Entitlement::where('organization_id', $user->organization_id)
                ->where('module_id', $module->id)
                ->first();
            $module->entitlement_status = $ent?->status;
            $module->is_entitled = $ent ? $ent->isActive() : false;
        }

        return response()->json($module);
    }

    /**
     * Start trial or activate free module
     */
    public function activate(Request $request, $slug)
    {
        $user = $request->user();

        if (!$user->hasPermission('module.activate') && !$user->hasPermission('module.purchase')) {
            return response()->json(['message' => 'دسترسی فعال‌سازی ندارید'], 403);
        }

        $module = Module::where('slug', $slug)->where('status', 'active')->firstOrFail();

        $existing = Entitlement::where('organization_id', $user->organization_id)
            ->where('module_id', $module->id)
            ->first();

        if ($existing && $existing->isActive()) {
            return response()->json(['message' => 'این ماژول قبلاً فعال است', 'entitlement' => $existing]);
        }

        // Free or trial activation (paid modules need payment flow - Phase 11+)
        if ($module->isFree()) {
            $entitlement = Entitlement::updateOrCreate(
                [
                    'organization_id' => $user->organization_id,
                    'module_id' => $module->id,
                ],
                [
                    'status' => 'active',
                    'source' => 'grant',
                    'starts_at' => now(),
                    'ends_at' => null,
                ]
            );
        } elseif ($module->trial_days > 0) {
            $entitlement = Entitlement::updateOrCreate(
                [
                    'organization_id' => $user->organization_id,
                    'module_id' => $module->id,
                ],
                [
                    'status' => 'trial',
                    'source' => 'trial',
                    'starts_at' => now(),
                    'trial_ends_at' => now()->addDays($module->trial_days),
                    'ends_at' => now()->addDays($module->trial_days),
                ]
            );
        } else {
            return response()->json([
                'message' => 'این ماژول نیاز به پرداخت دارد. سیستم پرداخت در فاز بعدی فعال می‌شود.',
                'module' => $module,
                'requires_payment' => true,
            ], 402);
        }

        return response()->json([
            'message' => 'ماژول فعال شد',
            'entitlement' => $entitlement->load('module'),
        ]);
    }

    /**
     * List entitlements of current organization
     */
    public function myModules(Request $request)
    {
        $user = $request->user();

        $entitlements = Entitlement::where('organization_id', $user->organization_id)
            ->with('module')
            ->latest()
            ->get();

        return response()->json(['entitlements' => $entitlements]);
    }
}
