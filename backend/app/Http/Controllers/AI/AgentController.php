<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiAgent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    /**
     * List available AI agents (system + org-specific)
     */
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
            ->get(['id', 'name', 'slug', 'role', 'department', 'description', 'status', 'skills']);

        return response()->json(['agents' => $agents]);
    }

    /**
     * Show single agent
     */
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
}
