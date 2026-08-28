<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function show(Request $request)
    {
        $org = $request->user()->organization;

        if (!$org) {
            return response()->json(['message' => 'سازمان یافت نشد'], 404);
        }

        return response()->json(['organization' => $org]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->name;

        if (!in_array($role, ['super_admin', 'admin'], true)) {
            return response()->json(['message' => 'دسترسی ندارید'], 403);
        }

        $org = $user->organization;
        if (!$org) {
            return response()->json(['message' => 'سازمان یافت نشد'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'settings' => 'sometimes|array',
            'settings.timezone' => 'nullable|string|max:64',
            'settings.locale' => 'nullable|string|max:10',
            'settings.notify_email' => 'nullable|email',
        ]);

        if (isset($validated['name'])) {
            $org->name = $validated['name'];
        }

        if (isset($validated['settings'])) {
            $org->settings = array_merge($org->settings ?? [], $validated['settings']);
        }

        $org->save();

        return response()->json([
            'message' => 'تنظیمات سازمان ذخیره شد',
            'organization' => $org->fresh(),
        ]);
    }
}
