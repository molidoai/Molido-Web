<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailTestController extends Controller
{
    /**
     * Send a test email to verify SMTP configuration.
     */
    public function test(Request $request)
    {
        $user = $request->user();
        $role = $user->role?->name;

        if (!in_array($role, ['super_admin', 'admin'], true)) {
            return response()->json(['message' => 'دسترسی ندارید'], 403);
        }

        $validated = $request->validate([
            'to' => 'nullable|email',
        ]);

        $to = $validated['to'] ?? $user->email;

        try {
            Mail::raw(
                "تست ارسال ایمیل سرور MOLIDO\nزمان: " . now()->toDateTimeString() . "\nبه: {$to}",
                function ($message) use ($to) {
                    $message->to($to)->subject('MOLIDO — تست ایمیل سرور');
                }
            );

            return response()->json([
                'message' => 'ایمیل تست ارسال شد',
                'to' => $to,
                'mailer' => config('mail.default'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'ارسال ناموفق',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
