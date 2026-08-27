<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\PaymentTransaction;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Initiate payment for a module
     */
    public function initiate(Request $request)
    {
        $user = $request->user();

        if (!$user->hasPermission('payment.create') && !$user->hasPermission('module.purchase')) {
            return response()->json(['message' => 'دسترسی پرداخت ندارید'], 403);
        }

        $validated = $request->validate([
            'module_slug' => 'required|string',
            'idempotency_key' => 'nullable|string|max:100',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $module = Module::where('slug', $validated['module_slug'])
            ->where('status', 'active')
            ->firstOrFail();

        if ($module->isFree()) {
            return response()->json([
                'message' => 'این ماژول رایگان است. از activate استفاده کنید.',
            ], 400);
        }

        $service = app(PaymentService::class);
        $result = $service->initiateModulePayment(
            $user->organization_id,
            $user->id,
            $module,
            $validated['customer_id'] ?? null,
            $validated['idempotency_key'] ?? null
        );

        return response()->json([
            'message' => 'تراکنش ایجاد شد',
            'transaction' => $result['transaction'],
            'redirect_url' => $result['redirect_url'] ?? null,
            'already_exists' => $result['already_exists'] ?? false,
        ]);
    }

    /**
     * Mock callback (for development)
     * Real providers will hit a dedicated callback route.
     */
    public function mockCallback(Request $request)
    {
        $uuid = $request->get('uuid');
        $token = $request->get('token');
        $status = $request->get('status', 'ok');

        if (!$uuid) {
            return response()->json(['message' => 'Missing uuid'], 400);
        }

        $service = app(PaymentService::class);
        $result = $service->verifyAndActivate($uuid, [
            'token' => $token,
            'status' => $status,
        ]);

        return response()->json($result);
    }

    /**
     * Generic verify endpoint (provider callback can POST here)
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid',
        ]);

        $service = app(PaymentService::class);
        $result = $service->verifyAndActivate(
            $validated['uuid'],
            $request->all()
        );

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    /**
     * List transactions for organization
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user->hasPermission('payment.view')) {
            return response()->json(['message' => 'دسترسی ندارید'], 403);
        }

        $transactions = PaymentTransaction::where('organization_id', $user->organization_id)
            ->with('module:id,name,slug')
            ->latest()
            ->paginate(20);

        return response()->json($transactions);
    }

    public function show(Request $request, $uuid)
    {
        $user = $request->user();

        $tx = PaymentTransaction::where('organization_id', $user->organization_id)
            ->where('uuid', $uuid)
            ->with('module')
            ->firstOrFail();

        return response()->json($tx);
    }
}
