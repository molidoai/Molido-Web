<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $agents = AiAgent::where(function ($q) use ($user) {
                $q->where('is_system', true)
                  ->orWhere('organization_id', $user->organization_id);
            })
            ->where('status', '!=', 'disabled')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json(['agents' => $agents]);
    }

    public function show(Request $request, $slug)
    {
        $user = $request->user();

        $agent = AiAgent::where('slug', $slug)
            ->where(function ($q) use ($user) {
                $q->where('is_system', true)
                  ->orWhere('organization_id', $user->organization_id);
            })
            ->firstOrFail();

        return response()->json($agent);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->name;

        if (!in_array($role, ['super_admin', 'admin'], true)) {
            if (method_exists($user, 'hasPermission') && !$user->hasPermission('ai.workforce.manage')) {
                return response()->json(['message' => 'دسترسی ساخت کارمند مجازی ندارید'], 403);
            }
            if (!method_exists($user, 'hasPermission')) {
                return response()->json(['message' => 'دسترسی ساخت کارمند مجازی ندارید'], 403);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'role' => 'required|string|max:60',
            'department' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:1000',
            'system_instructions' => 'required|string|max:8000',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:80',
            'tools' => 'nullable|array',
            'status' => 'nullable|in:available,busy,disabled',
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug . '-org' . $user->organization_id;
        $n = 1;
        while (AiAgent::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-org' . $user->organization_id . '-' . $n++;
        }

        $agent = AiAgent::create([
            'organization_id' => $user->organization_id,
            'name' => $validated['name'],
            'slug' => $slug,
            'role' => $validated['role'],
            'department' => $validated['department'] ?? 'Custom',
            'description' => $validated['description'] ?? null,
            'system_instructions' => $validated['system_instructions'],
            'skills' => $validated['skills'] ?? [],
            'tools' => $validated['tools'] ?? [],
            'permissions' => [],
            'status' => $validated['status'] ?? 'available',
            'is_system' => false,
        ]);

        try {
            AuditService::log('ai.agent.created', [
                'organization_id' => $user->organization_id,
                'actor_type' => 'user',
                'actor_id' => $user->id,
                'entity_type' => 'ai_agent',
                'entity_id' => $agent->id,
                'metadata' => ['name' => $agent->name, 'slug' => $agent->slug],
            ]);
        } catch (\Throwable $e) {
        }

        return response()->json([
            'message' => 'کارمند مجازی ساخته شد',
            'agent' => $agent,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $agent = AiAgent::where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->where('is_system', false)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:120',
            'role' => 'sometimes|string|max:60',
            'department' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:1000',
            'system_instructions' => 'sometimes|string|max:8000',
            'skills' => 'nullable|array',
            'tools' => 'nullable|array',
            'status' => 'nullable|in:available,busy,disabled',
        ]);

        $agent->update($validated);

        return response()->json([
            'message' => 'کارمند مجازی به‌روز شد',
            'agent' => $agent->fresh(),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $agent = AiAgent::where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->where('is_system', false)
            ->firstOrFail();

        $agent->delete();

        return response()->json(['message' => 'کارمند مجازی حذف شد']);
    }

    public function templates()
    {
        return response()->json([
            'templates' => [
                [
                    'key' => 'sales',
                    'name' => 'کارمند فروش',
                    'role' => 'sales',
                    'department' => 'Sales',
                    'system_instructions' => "You are a virtual sales employee.\nHelp with leads and follow-ups.\nNever invent customer data.",
                    'skills' => ['lead_management', 'follow_up', 'deal_pipeline'],
                ],
                [
                    'key' => 'support',
                    'name' => 'پشتیبانی مشتری',
                    'role' => 'support',
                    'department' => 'Support',
                    'system_instructions' => "You are a virtual support agent.\nAnswer FAQs and draft ticket replies.\nEscalate sensitive issues.",
                    'skills' => ['ticket_help', 'faq', 'escalation'],
                ],
                [
                    'key' => 'hr',
                    'name' => 'منابع انسانی',
                    'role' => 'hr',
                    'department' => 'HR',
                    'system_instructions' => "You are a virtual HR assistant.\nDraft job descriptions and onboarding checklists.",
                    'skills' => ['job_description', 'onboarding'],
                ],
                [
                    'key' => 'ops',
                    'name' => 'عملیات',
                    'role' => 'operations',
                    'department' => 'Operations',
                    'system_instructions' => "You are a virtual operations assistant.\nHelp with process checklists and coordination.",
                    'skills' => ['process', 'checklist'],
                ],
            ],
        ]);
    }
}
