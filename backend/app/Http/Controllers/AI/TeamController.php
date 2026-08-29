<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use App\Models\AiTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $teams = AiTeam::where(function ($q) use ($user) {
                $q->where('is_system', true)
                  ->orWhere('organization_id', $user->organization_id);
            })
            ->where('status', 'active')
            ->with(['agents:id,name,slug,role,department', 'leadAgent:id,name,slug'])
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json(['teams' => $teams]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->name;
        if (!in_array($role, ['super_admin', 'admin'], true)) {
            return response()->json(['message' => 'فقط مدیر می‌تواند تیم بسازد'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'department' => 'nullable|string|max:80',
            'description' => 'nullable|string|max:1000',
            'agent_ids' => 'required|array|min:1',
            'agent_ids.*' => 'integer|exists:ai_agents,id',
            'lead_agent_id' => 'nullable|integer|exists:ai_agents,id',
            'routing_rules' => 'nullable|array',
        ]);

        // Agents must be system or org-owned
        $agents = AiAgent::whereIn('id', $validated['agent_ids'])
            ->where(function ($q) use ($user) {
                $q->where('is_system', true)
                  ->orWhere('organization_id', $user->organization_id);
            })
            ->get();

        if ($agents->count() < 1) {
            return response()->json(['message' => 'ایجنت معتبری انتخاب نشده'], 422);
        }

        $base = Str::slug($validated['name']);
        $slug = $base . '-org' . $user->organization_id;
        $n = 1;
        while (AiTeam::withTrashed()->where('organization_id', $user->organization_id)->where('slug', $slug)->exists()) {
            $slug = $base . '-org' . $user->organization_id . '-' . $n++;
        }

        $leadId = $validated['lead_agent_id'] ?? $agents->first()->id;
        if (!$agents->contains('id', $leadId)) {
            $leadId = $agents->first()->id;
        }

        $team = AiTeam::create([
            'organization_id' => $user->organization_id,
            'name' => $validated['name'],
            'slug' => $slug,
            'department' => $validated['department'] ?? null,
            'description' => $validated['description'] ?? null,
            'lead_agent_id' => $leadId,
            'routing_rules' => $validated['routing_rules'] ?? $this->defaultRules($agents),
            'status' => 'active',
            'is_system' => false,
        ]);

        $sync = [];
        foreach ($agents->values() as $i => $agent) {
            $sync[$agent->id] = [
                'sort_order' => $i,
                'member_role' => $agent->id === $leadId ? 'lead' : 'specialist',
            ];
        }
        $team->agents()->sync($sync);

        return response()->json([
            'message' => 'تیم AI ساخته شد',
            'team' => $team->load(['agents', 'leadAgent']),
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $team = AiTeam::where('id', $id)
            ->where('organization_id', $user->organization_id)
            ->where('is_system', false)
            ->firstOrFail();

        $team->delete();

        return response()->json(['message' => 'تیم حذف شد']);
    }

    protected function defaultRules($agents): array
    {
        $rules = [];
        foreach ($agents as $agent) {
            $kw = match ($agent->role) {
                'sales' => ['فروش', 'سرنخ', 'قیمت', 'lead', 'deal'],
                'support' => ['پشتیبانی', 'تیکت', 'مشکل', 'support'],
                'finance' => ['مالی', 'پرداخت', 'فاکتور', 'invoice'],
                'hr' => ['استخدام', 'hr', 'نیرو'],
                default => [],
            };
            if ($kw) {
                $rules[] = ['keywords' => $kw, 'agent_slug' => $agent->slug];
            }
        }
        return $rules;
    }
}
