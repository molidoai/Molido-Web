<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Deal::where('organization_id', $user->organization_id)
            ->with(['customer:id,first_name,last_name,email', 'assignee:id,name'])
            ->latest();

        if ($stage = $request->get('stage')) {
            $query->where('stage', $stage);
        }

        return response()->json($query->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'assigned_to' => 'nullable|exists:users,id',
            'stage' => 'nullable|in:prospecting,qualification,proposal,negotiation,won,lost',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Tenant check for customer
        $customerOk = \App\Models\Customer::where('id', $validated['customer_id'])
            ->where('organization_id', $user->organization_id)
            ->exists();

        if (!$customerOk) {
            return response()->json(['message' => 'مشتری متعلق به سازمان شما نیست'], 403);
        }

        $deal = Deal::create([
            ...$validated,
            'organization_id' => $user->organization_id,
            'stage' => $validated['stage'] ?? 'prospecting',
            'amount' => $validated['amount'] ?? 0,
            'currency' => $validated['currency'] ?? 'IRR',
            'probability' => $validated['probability'] ?? 10,
        ]);

        return response()->json([
            'message' => 'معامله ایجاد شد',
            'deal' => $deal->load(['customer', 'assignee']),
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $deal = Deal::where('organization_id', $user->organization_id)
            ->with(['customer', 'assignee', 'lead'])
            ->findOrFail($id);

        return response()->json($deal);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        $deal = Deal::where('organization_id', $user->organization_id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'customer_id' => 'sometimes|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'assigned_to' => 'nullable|exists:users,id',
            'stage' => 'nullable|in:prospecting,qualification,proposal,negotiation,won,lost',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'probability' => 'nullable|integer|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $deal->update($validated);

        return response()->json([
            'message' => 'معامله به‌روزرسانی شد',
            'deal' => $deal->fresh()->load(['customer', 'assignee']),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $deal = Deal::where('organization_id', $user->organization_id)->findOrFail($id);
        $deal->delete();

        return response()->json(['message' => 'معامله حذف شد']);
    }
}
