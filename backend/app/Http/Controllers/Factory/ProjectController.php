<?php

namespace App\Http\Controllers\Factory;

use App\Http\Controllers\Controller;
use App\Models\FactoryProject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function templates()
    {
        $list = [];
        foreach (FactoryProject::TEMPLATES as $key => $label) {
            $list[] = ['key' => $key, 'label' => $label];
        }

        return response()->json(['templates' => $list]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $items = FactoryProject::where('organization_id', $user->organization_id)
            ->with(['defaultTeam:id,name,slug', 'defaultAgent:id,name,slug'])
            ->latest()
            ->paginate($request->get('per_page', 20));

        return response()->json($items);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'template' => 'required|in:' . implode(',', array_keys(FactoryProject::TEMPLATES)),
            'description' => 'nullable|string|max:2000',
            'default_team_id' => 'nullable|integer',
            'default_agent_id' => 'nullable|integer',
            'monthly_token_budget' => 'nullable|integer|min:0',
            'ai_config' => 'nullable|array',
        ]);

        $base = Str::slug($validated['name']);
        $slug = $base;
        $n = 1;
        while (
            FactoryProject::withTrashed()
                ->where('organization_id', $user->organization_id)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $n++;
        }

        $project = FactoryProject::create([
            'organization_id' => $user->organization_id,
            'created_by' => $user->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'template' => $validated['template'],
            'status' => 'draft',
            'description' => $validated['description'] ?? null,
            'default_team_id' => $validated['default_team_id'] ?? null,
            'default_agent_id' => $validated['default_agent_id'] ?? null,
            'monthly_token_budget' => $validated['monthly_token_budget'] ?? null,
            'ai_config' => $validated['ai_config'] ?? [
                'provider' => env('AI_PROVIDER', 'openai'),
                'model' => env('AI_MODEL', 'gpt-4o-mini'),
            ],
            'security_config' => [
                'require_approval_for_actions' => true,
                'prompt_injection_filter' => true,
            ],
        ]);

        return response()->json([
            'message' => 'پروژه AI Factory ایجاد شد',
            'project' => $project->load(['defaultTeam', 'defaultAgent']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $project = FactoryProject::where('organization_id', $user->organization_id)
            ->with(['defaultTeam', 'defaultAgent', 'creator:id,name,email'])
            ->findOrFail($id);

        // Digital twin — real fields only (no fake status)
        $twin = [
            'code' => 'monolith-shared',
            'database' => 'shared-tenant',
            'ai_gateway' => config('app.name') . '-gateway',
            'agents' => $project->default_agent_id ? 'linked' : 'none',
            'teams' => $project->default_team_id ? 'linked' : 'none',
            'status' => $project->status,
            'budget_tokens_month' => $project->monthly_token_budget,
        ];

        return response()->json([
            'project' => $project,
            'digital_twin' => $twin,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        $project = FactoryProject::where('organization_id', $user->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:120',
            'description' => 'nullable|string|max:2000',
            'status' => 'sometimes|in:draft,active,paused,archived',
            'default_team_id' => 'nullable|integer',
            'default_agent_id' => 'nullable|integer',
            'monthly_token_budget' => 'nullable|integer|min:0',
            'ai_config' => 'nullable|array',
            'security_config' => 'nullable|array',
        ]);

        $project->update($validated);

        return response()->json([
            'message' => 'پروژه به‌روز شد',
            'project' => $project->fresh()->load(['defaultTeam', 'defaultAgent']),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $project = FactoryProject::where('organization_id', $user->organization_id)->findOrFail($id);
        $project->delete();

        return response()->json(['message' => 'پروژه آرشیو/حذف شد']);
    }
}
