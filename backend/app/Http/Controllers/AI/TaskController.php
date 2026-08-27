<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\AiTask;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * AI Task Inbox — list tasks for organization
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = AiTask::where('organization_id', $user->organization_id)
            ->with(['agent:id,name,slug,role', 'user:id,name', 'customer:id,first_name,last_name'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->get('priority')) {
            $query->where('priority', $priority);
        }

        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|string|max:50',
            'priority' => 'nullable|in:low,medium,high,critical',
            'agent_id' => 'nullable|exists:ai_agents,id',
            'customer_id' => 'nullable|exists:customers,id',
            'conversation_id' => 'nullable|exists:ai_conversations,id',
            'input' => 'nullable|array',
        ]);

        $task = AiTask::create([
            'organization_id' => $user->organization_id,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'] ?? 'general',
            'priority' => $validated['priority'] ?? 'medium',
            'agent_id' => $validated['agent_id'] ?? null,
            'customer_id' => $validated['customer_id'] ?? null,
            'conversation_id' => $validated['conversation_id'] ?? null,
            'input' => $validated['input'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تسک ایجاد شد',
            'task' => $task->load(['agent', 'user']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $task = AiTask::where('organization_id', $user->organization_id)
            ->with(['agent', 'user', 'customer', 'approvals'])
            ->findOrFail($id);

        return response()->json($task);
    }

    public function updateStatus(Request $request, $id)
    {
        $user = $request->user();

        $task = AiTask::where('organization_id', $user->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,working,waiting_approval,completed,failed,cancelled',
            'result' => 'nullable|array',
            'error' => 'nullable|string',
        ]);

        $data = ['status' => $validated['status']];

        if ($validated['status'] === 'working' && !$task->started_at) {
            $data['started_at'] = now();
        }

        if (in_array($validated['status'], ['completed', 'failed', 'cancelled'])) {
            $data['completed_at'] = now();
            if (isset($validated['result'])) {
                $data['result'] = $validated['result'];
            }
            if (isset($validated['error'])) {
                $data['error'] = $validated['error'];
            }
        }

        $task->update($data);

        return response()->json([
            'message' => 'وضعیت تسک به‌روزرسانی شد',
            'task' => $task->fresh(),
        ]);
    }
}
