<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>خوش آمدید</title>
</head>
<body style="font-family: Tahoma, Arial, sans-serif; background:#0f172a; color:#e2e8f0; padding:24px;">
    <div style="max-width:560px;margin:0 auto;background:#1e293b;border-radius:16px;padding:28px;border:1px solid #334155;">
        <h1 style="color:#22d3ee;font-size:22px;margin:0 0 12px;">{{ $appName }}</h1>
        <p style="margin:0 0 16px;line-height:1.8;">
            سلام {{ $user->name }}،
        </p>
        <p style="margin:0 0 16px;line-height:1.8;">
            ثبت‌نام شما با موفقیت انجام شد. سازمان شما ساخته شده و می‌توانید وارد مرکز فرمان شوید.
        </p>
        <p style="margin:0 0 20px;line-height:1.8;">
            ایمیل حساب: <strong style="color:#fff;">{{ $user->email }}</strong>
        </p>
        <p style="margin:0 0 24px;">
            <a href="{{ $frontendUrl }}/login"
               style="display:inline-block;background:#06b6d4;color:#0f172a;text-decoration:none;padding:12px 20px;border-radius:999px;font-weight:bold;">
                ورود به پنل
            </a>
        </p>
        <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
            اگر شما این ثبت‌نام را انجام نداده‌اید، این پیام را نادیده بگیرید.
        </p>
    </div>
</body>
</html>
