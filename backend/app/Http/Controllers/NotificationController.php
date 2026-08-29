<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $items = AppNotification::where('user_id', $user->id)
            ->latest()
            ->limit(50)
            ->get();

        $unread = AppNotification::where('user_id', $user->id)->whereNull('read_at')->count();

        return response()->json([
            'notifications' => $items,
            'unread' => $unread,
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $n = AppNotification::where('user_id', $request->user()->id)->findOrFail($id);
        $n->update(['read_at' => now()]);

        return response()->json(['message' => 'خوانده شد', 'notification' => $n]);
    }

    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['message' => 'همه خوانده شدند']);
    }
}
