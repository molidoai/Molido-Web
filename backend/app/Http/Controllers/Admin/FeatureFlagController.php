<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureFlag;
use Illuminate\Http\Request;

class FeatureFlagController extends Controller
{
    /**
     * List all feature flags
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->hasPermission('admin.settings.manage') && !$user->hasPermission('admin.users.manage')) {
            // Allow read for authenticated admins via role name fallback
            $role = $user->role?->name;
            if (!in_array($role, ['super_admin', 'admin'])) {
                return response()->json(['message' => 'دسترسی ندارید'], 403);
            }
        }

        $flags = FeatureFlag::orderBy('key')->get();

        return response()->json(['flags' => $flags]);
    }

    /**
     * Public-ish list of enabled flags for frontend (authenticated)
     */
    public function enabled(Request $request)
    {
        $flags = FeatureFlag::where('enabled', true)->pluck('key');

        return response()->json(['enabled' => $flags]);
    }

    /**
     * Update a flag
     */
    public function update(Request $request, $key)
    {
        $user = $request->user();

        $role = $user->role?->name;
        if (!in_array($role, ['super_admin', 'admin']) && !$user->hasPermission('admin.settings.manage')) {
            return response()->json(['message' => 'دسترسی ندارید'], 403);
        }

        $validated = $request->validate([
            'enabled' => 'required|boolean',
            'config' => 'nullable|array',
        ]);

        $flag = FeatureFlag::firstOrCreate(
            ['key' => $key],
            ['enabled' => false]
        );

        $flag->update([
            'enabled' => $validated['enabled'],
            'config' => $validated['config'] ?? $flag->config,
        ]);

        FeatureFlag::clearCache($key);

        return response()->json([
            'message' => 'فلگ به‌روزرسانی شد',
            'flag' => $flag->fresh(),
        ]);
    }

    /**
     * Check single flag
     */
    public function check($key)
    {
        return response()->json([
            'key' => $key,
            'enabled' => FeatureFlag::isEnabled($key),
        ]);
    }
}
