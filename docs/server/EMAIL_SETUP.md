# MOLIDO — فعال‌سازی ایمیل سرور (SMTP)

## ۱. تنظیم `.env`

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=hidooch980@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hidooch980@gmail.com
MAIL_FROM_NAME="MOLIDO"
```

> برای Gmail باید **App Password** بسازی (نه رمز اصلی جیمیل).  
> Google Account → Security → 2-Step Verification → App passwords.

### گزینه‌های دیگر

| سرویس | HOST | PORT |
|--------|------|------|
| Gmail | smtp.gmail.com | 587 (tls) |
| Mailgun | smtp.mailgun.org | 587 |
| Zoho | smtp.zoho.com | 587 |
| سرور cPanel | mail.yourdomain.com | 465/ssl یا 587/tls |

---

## ۲. تست ارسال

```bash
cd backend
php artisan tinker
```

```php
Mail::raw('تست MOLIDO', function ($m) {
    $m->to('hidooch980@gmail.com')->subject('Test MOLIDO Mail');
});
```

یا از API (بعد از لاگین ادمین):

```http
POST /api/v1/mail/test
Authorization: Bearer {token}
```

---

## ۳. رفتار فعلی پروژه

- بعد از **ثبت‌نام موفق**، ایمیل خوش‌آمد (`WelcomeRegistered`) ارسال می‌شود.
- اگر SMTP قطع باشد، ثبت‌نام **قطع نمی‌شود**؛ خطا فقط در لاگ ثبت می‌شود.
- در حالت توسعه می‌توانی بگذاری: `MAIL_MAILER=log` (ایمیل در `storage/logs` نوشته می‌شود).

---

## ۴. صف (توصیه در پروداکشن)

```env
QUEUE_CONNECTION=database
```

و Mailable را `ShouldQueue` کن یا worker را روشن نگه دار:

```bash
php artisan queue:work
```
