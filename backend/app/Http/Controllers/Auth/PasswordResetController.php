<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class PasswordResetController extends Controller
{
    /**
     * Request password reset link (token emailed).
     */
    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Always same response (don't leak whether email exists)
        $message = 'اگر این ایمیل ثبت شده باشد، لینک بازیابی ارسال می‌شود.';

        if (!$user) {
            return response()->json(['message' => $message]);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $frontend = rtrim(env('FRONTEND_URL', config('app.url')), '/');
        $link = $frontend . '/reset-password?email=' . urlencode($user->email) . '&token=' . $token;

        try {
            Mail::raw(
                "بازیابی رمز MOLIDO\n\nروی لینک زیر کلیک کنید:\n{$link}\n\nاگر درخواست نداده‌اید، نادیده بگیرید.",
                function ($m) use ($user) {
                    $m->to($user->email)->subject('بازیابی رمز — MOLIDO');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Password reset mail failed', ['error' => $e->getMessage()]);
            // In log mailer / dev, still return token for testing only when APP_DEBUG
            if (config('app.debug')) {
                return response()->json([
                    'message' => $message,
                    'debug_link' => $link,
                ]);
            }
        }

        return response()->json(['message' => $message]);
    }

    /**
     * Reset password with token.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $row = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$row || !Hash::check($request->token, $row->token)) {
            return response()->json(['message' => 'توکن نامعتبر یا منقضی است'], 422);
        }

        if ($row->created_at && now()->diffInMinutes($row->created_at) > 60) {
            return response()->json(['message' => 'توکن منقضی شده است'], 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'رمز عبور با موفقیت تغییر کرد']);
    }
}
