<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Lead::where('organization_id', $user->organization_id)
            ->with(['customer:id,first_name,last_name,email', 'assignee:id,name'])
            ->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,converted,lost',
            'priority' => 'nullable|in:low,medium,high',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'next_follow_up_at' => 'nullable|date',
        ]);

        // Ensure customer belongs to same org
        if (!empty($validated['customer_id'])) {
            $exists = \App\Models\Customer::where('id', $validated['customer_id'])
                ->where('organization_id', $user->organization_id)
                ->exists();
            if (!$exists) {
                return response()->json(['message' => 'مشتری متعلق به سازمان شما نیست'], 403);
            }
        }

        $lead = Lead::create([
            ...$validated,
            'organization_id' => $user->organization_id,
            'status' => $validated['status'] ?? 'new',
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        return response()->json([
            'message' => 'سرنخ ایجاد شد',
            'lead' => $lead->load(['customer', 'assignee']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $lead = Lead::where('organization_id', $user->organization_id)
            ->with(['customer', 'assignee'])
            ->findOrFail($id);

        return response()->json($lead);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $lead = Lead::where('organization_id', $user->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'assigned_to' => 'nullable|exists:users,id',
            'source' => 'nullable|string|max:100',
            'status' => 'nullable|in:new,contacted,qualified,converted,lost',
            'priority' => 'nullable|in:low,medium,high',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'next_follow_up_at' => 'nullable|date',
        ]);

        $lead->update($validated);

        return response()->json([
            'message' => 'سرنخ به‌روزرسانی شد',
            'lead' => $lead->fresh()->load(['customer', 'assignee']),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $lead = Lead::where('organization_id', $user->organization_id)->findOrFail($id);
        $lead->delete();

        return response()->json(['message' => 'سرنخ حذف شد']);
    }
}
