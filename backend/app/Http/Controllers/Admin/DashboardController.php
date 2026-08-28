<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiTask;
use App\Models\Customer;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $orgId = $request->user()->organization_id;

        $paidSum = PaymentTransaction::where('organization_id', $orgId)
            ->where('status', 'paid')
            ->sum('amount');

        // Last 7 days payments count
        $paymentsByDay = PaymentTransaction::where('organization_id', $orgId)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->select(DB::raw('DATE(created_at) as day'), DB::raw('count(*) as count'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $tasksByStatus = AiTask::where('organization_id', $orgId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'counts' => [
                'customers' => Customer::where('organization_id', $orgId)->count(),
                'leads' => Lead::where('organization_id', $orgId)->count(),
                'deals' => Deal::where('organization_id', $orgId)->count(),
                'products' => Product::where('organization_id', $orgId)->count(),
                'orders' => Order::where('organization_id', $orgId)->count(),
                'tasks' => AiTask::where('organization_id', $orgId)->count(),
                'subscriptions_active' => Subscription::where('organization_id', $orgId)
                    ->whereIn('status', ['active', 'trialing'])
                    ->count(),
            ],
            'revenue' => [
                'paid_total' => (float) $paidSum,
                'paid_transactions' => PaymentTransaction::where('organization_id', $orgId)
                    ->where('status', 'paid')
                    ->count(),
            ],
            'charts' => [
                'payments_last_7_days' => $paymentsByDay,
                'tasks_by_status' => $tasksByStatus,
            ],
        ]);
    }
}
