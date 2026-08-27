<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('permission:crm.customer.read')
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'احراز هویت نشده'], 401);
        }

        if (!$user->hasPermission($permission)) {
            return response()->json([
                'message' => 'دسترسی غیرمجاز',
                'required_permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
