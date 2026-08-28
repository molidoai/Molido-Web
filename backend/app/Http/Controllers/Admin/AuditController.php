<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->name;

        if (!in_array($role, ['super_admin', 'admin']) && !$user->hasPermission('admin.audit.view')) {
            return response()->json(['message' => 'دسترسی ندارید'], 403);
        }

        $query = AuditLog::where('organization_id', $user->organization_id)
            ->latest();

        if ($action = $request->get('action')) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($result = $request->get('result')) {
            $query->where('result', $result);
        }

        return response()->json($query->paginate($request->get('per_page', 30)));
    }
}
