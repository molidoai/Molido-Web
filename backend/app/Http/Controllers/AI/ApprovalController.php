<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\AiTask;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * List pending approvals for organization
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->hasPermission('ai.agent.approve')) {
            return response()->json(['message' => 'دسترسی تأیید ندارید'], 403);
        }

        $query = Approval::where('organization_id', $user->organization_id)
            ->with(['task', 'requester:id,name'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        } else {
            $query->where('status', 'pending');
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Create approval request (usually called by AI / system)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'task_id' => 'nullable|exists:ai_tasks,id',
            'action' => 'required|string|max:100',
            'reason' => 'nullable|string',
            'payload' => 'nullable|array',
            'risk_level' => 'nullable|in:low,medium,high,critical',
        ]);

        $approval = Approval::create([
            'organization_id' => $user->organization_id,
            'task_id' => $validated['task_id'] ?? null,
            'requested_by' => $user->id,
            'action' => $validated['action'],
            'reason' => $validated['reason'] ?? null,
            'payload' => $validated['payload'] ?? null,
            'status' => 'pending',
            'risk_level' => $validated['risk_level'] ?? 'medium',
            'expires_at' => now()->addHours(24),
        ]);

        // If linked to task, mark task waiting_approval
        if ($approval->task_id) {
            AiTask::where('id', $approval->task_id)
                ->where('organization_id', $user->organization_id)
                ->update(['status' => 'waiting_approval']);
        }

        return response()->json([
            'message' => 'درخواست تأیید ایجاد شد',
            'approval' => $approval,
        ], 201);
    }

    /**
     * Approve or reject
     */
    public function review(Request $request, $id)
    {
        $user = $request->user();

        if (!$user->hasPermission('ai.agent.approve')) {
            return response()->json(['message' => 'دسترسی تأیید ندارید'], 403);
        }

        $approval = Approval::where('organization_id', $user->organization_id)
            ->where('status', 'pending')
            ->findOrFail($id);

        $validated = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $approval->update([
            'status' => $validated['decision'],
            'reviewed_by' => $user->id,
            'review_note' => $validated['review_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        // Update linked task
        if ($approval->task_id) {
            $taskStatus = $validated['decision'] === 'approved' ? 'working' : 'cancelled';
            AiTask::where('id', $approval->task_id)->update(['status' => $taskStatus]);
        }

        // TODO: If approved, execute the payload action via dedicated handlers

        return response()->json([
            'message' => $validated['decision'] === 'approved' ? 'تأیید شد' : 'رد شد',
            'approval' => $approval->fresh(),
        ]);
    }
}
