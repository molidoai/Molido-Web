<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use App\Services\AuditService;

class AuthController extends Controller
{
    /**
     * Register a new user + organization (for first user)
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'organization_name' => 'required|string|max:255',
        ]);

        // Create organization
        $organization = Organization::create([
            'name' => $request->organization_name,
            'slug' => Str::slug($request->organization_name) . '-' . Str::random(4),
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);

        // Assign Admin role
        $adminRole = Role::where('name', 'admin')->first();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'organization_id' => $organization->id,
            'role_id' => $adminRole?->id,
            'status' => 'active',
            'locale' => 'fa',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'ثبت‌نام با موفقیت انجام شد',
            'user' => $user->load('role', 'organization'),
            'token' => $token,
        ], 201);
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $key = 'login:' . Str::lower($request->email) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["تعداد تلاش‌ها زیاد است. لطفاً {$seconds} ثانیه دیگر تلاش کنید."],
            ]);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'email' => ['ایمیل یا رمز عبور اشتباه است.'],
            ]);
        }

        RateLimiter::clear($key);

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => ['حساب کاربری شما غیرفعال است.'],
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $token = $user->createToken('auth-token')->plainTextToken;

        AuditService::log('auth.login', [
            'organization_id' => $user->organization_id,
            'actor_type' => 'user',
            'actor_id' => $user->id,
            'result' => 'success',
        ]);

        return response()->json([
            'message' => 'ورود موفقیت‌آمیز',
            'user' => $user->load('role', 'organization'),
            'token' => $token,
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'خروج موفقیت‌آمیز',
        ]);
    }

    /**
     * Current user profile
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('role.permissions', 'organization');

        return response()->json([
            'user' => $user,
            'permissions' => $user->role?->permissions->pluck('name') ?? [],
        ]);
    }
}
