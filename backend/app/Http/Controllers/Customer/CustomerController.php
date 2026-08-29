<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * List customers of current organization (paginated)
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Customer::where('organization_id', $user->organization_id)
            ->with('user:id,name,email')
            ->latest();

        // Simple search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $customers = $query->paginate($request->get('per_page', 15));

        return response()->json($customers);
    }

    /**
     * Create a new customer (central identity)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')->where(fn ($q) => $q->where('organization_id', $user->organization_id)),
            ],
            'phone'      => 'nullable|string|max:30',
            'source'     => 'nullable|string|max:50',
            'status'     => 'nullable|in:active,inactive,lead',
            'metadata'   => 'nullable|array',
        ]);

        $customer = Customer::create([
            ...$validated,
            'organization_id' => $user->organization_id,
            'status' => $validated['status'] ?? 'active',
        ]);

        // TODO: Dispatch CustomerCreated event (for CRM / Chat / Notifications)

        return response()->json([
            'message' => 'مشتری با موفقیت ایجاد شد',
            'customer' => $customer,
        ], 201);
    }

    /**
     * Show single customer
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $customer = Customer::where('organization_id', $user->organization_id)
            ->with('user:id,name,email')
            ->findOrFail($id);

        return response()->json($customer);
    }

    /**
     * Update customer
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $customer = Customer::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers')
                    ->where(fn ($q) => $q->where('organization_id', $user->organization_id))
                    ->ignore($customer->id),
            ],
            'phone'      => 'nullable|string|max:30',
            'source'     => 'nullable|string|max:50',
            'status'     => 'nullable|in:active,inactive,lead',
            'metadata'   => 'nullable|array',
        ]);

        $customer->update($validated);

        return response()->json([
            'message' => 'مشتری به‌روزرسانی شد',
            'customer' => $customer->fresh(),
        ]);
    }

    /**
     * Soft delete customer
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $customer = Customer::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        $customer->delete();

        return response()->json([
            'message' => 'مشتری حذف شد',
        ]);
    }

    /**
     * Export customers as CSV (stream)
     */
    public function export(Request $request)
    {
        $user = $request->user();
        $rows = Customer::where('organization_id', $user->organization_id)
            ->orderBy('id')
            ->get(['id', 'first_name', 'last_name', 'email', 'phone', 'status', 'source', 'created_at']);

        $filename = 'customers-' . now()->format('Ymd-His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($out, ['id', 'first_name', 'last_name', 'email', 'phone', 'status', 'source', 'created_at']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->first_name,
                    $r->last_name,
                    $r->email,
                    $r->phone,
                    $r->status,
                    $r->source,
                    optional($r->created_at)->toDateTimeString(),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

}
