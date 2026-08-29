<?php

namespace App\Http\Controllers\Team;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\OrganizationInvite;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class InviteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $this->assertAdmin($user);

        $invites = OrganizationInvite::where('organization_id', $user->organization_id)
            ->with('role:id,name,display_name')
            ->latest()
            ->limit(50)
            ->get();

        $members = User::where('organization_id', $user->organization_id)
            ->with('role:id,name,display_name')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_id', 'status', 'created_at']);

        return response()->json([
            'members' => $members,
            'invites' => $invites,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $this->assertAdmin($user);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'nullable|string|max:50',
        ]);

        $email = Str::lower($validated['email']);

        if (User::where('email', $email)->where('organization_id', $user->organization_id)->exists()) {
            return response()->json(['message' => 'این کاربر هم‌اکنون عضو سازمان است'], 422);
        }

        $roleName = $validated['role'] ?? 'member';
        $role = Role::where('name', $roleName)->first() ?? Role::where('name', 'member')->first();

        $token = Str::random(48);

        $invite = OrganizationInvite::updateOrCreate(
            [
                'organization_id' => $user->organization_id,
                'email' => $email,
                'status' => 'pending',
            ],
            [
                'invited_by' => $user->id,
                'role_id' => $role?->id,
                'token' => $token,
                'expires_at' => now()->addDays(7),
            ]
        );

        $frontend = rtrim(env('FRONTEND_URL', config('app.url')), '/');
        $link = $frontend . '/accept-invite?token=' . $token;
        $orgName = $user->organization->name ?? 'سازمان';

        try {
            Mail::raw(
                "دعوت به سازمان در MOLIDO\n\nشما به سازمان «{$orgName}» دعوت شده‌اید.\nلینک پذیرش (۷ روز معتبر):\n{$link}\n",
                function ($m) use ($email) {
                    $m->to($email)->subject('دعوت به سازمان — MOLIDO');
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Invite mail failed', ['error' => $e->getMessage()]);
            if (config('app.debug')) {
                return response()->json([
                    'message' => 'دعوت ثبت شد (ایمیل ارسال نشد — حالت debug)',
                    'invite' => $invite,
                    'debug_link' => $link,
                ], 201);
            }
        }

        AppNotification::notify($user, 'دعوت ارسال شد', [
            'type' => 'invite',
            'body' => "دعوت برای {$email}",
            'link' => '/team',
        ]);

        return response()->json([
            'message' => 'دعوت ارسال شد',
            'invite' => $invite,
        ], 201);
    }

    public function revoke(Request $request, $id)
    {
        $user = $request->user();
        $this->assertAdmin($user);

        $invite = OrganizationInvite::where('organization_id', $user->organization_id)
            ->where('id', $id)
            ->firstOrFail();

        $invite->update(['status' => 'revoked']);

        return response()->json(['message' => 'دعوت لغو شد']);
    }

    public function preview(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $invite = OrganizationInvite::where('token', $request->token)
            ->with('organization:id,name', 'role:id,name,display_name')
            ->first();

        if (!$invite || !$invite->isValid()) {
            return response()->json(['message' => 'دعوت نامعتبر یا منقضی است'], 422);
        }

        return response()->json([
            'email' => $invite->email,
            'organization' => $invite->organization,
            'role' => $invite->role,
            'expires_at' => $invite->expires_at,
        ]);
    }

    public function accept(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
            'name' => 'required|string|max:255',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $invite = OrganizationInvite::where('token', $validated['token'])->first();

        if (!$invite || !$invite->isValid()) {
            return response()->json(['message' => 'دعوت نامعتبر یا منقضی است'], 422);
        }

        if (User::where('email', $invite->email)->exists()) {
            return response()->json(['message' => 'این ایمیل قبلاً ثبت شده'], 422);
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $invite->email,
            'password' => Hash::make($validated['password']),
            'organization_id' => $invite->organization_id,
            'role_id' => $invite->role_id,
            'status' => 'active',
            'locale' => 'fa',
        ]);

        $invite->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        AppNotification::notify($user, 'به سازمان خوش آمدید', [
            'type' => 'success',
            'body' => 'عضویت شما فعال شد',
            'link' => '/',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'عضویت با موفقیت انجام شد',
            'user' => $user->load('role', 'organization'),
            'token' => $token,
        ], 201);
    }

    protected function assertAdmin($user): void
    {
        $role = $user->role?->name;
        if (!in_array($role, ['super_admin', 'admin'], true)) {
            abort(403, 'فقط مدیر می‌تواند اعضا را مدیریت کند');
        }
    }
}
