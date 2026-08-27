# MOLIDO CORE — cPanel Deployment Guide

MOLIDO is designed to remain deployable on standard shared hosting with cPanel.

## Requirements

- PHP 8.3+ (or 8.2 minimum)
- MySQL 8 / MariaDB
- Composer (SSH or cPanel Terminal)
- SSL (AutoSSL / Let's Encrypt)
- Cron Jobs
- Apache (mod_rewrite)

**Not required:** Docker, Redis, Kubernetes, GPU, Vector DB.

---

## 1. Create Database

1. cPanel → MySQL Databases
2. Create database + user
3. Grant all privileges
4. Note: host, database name, username, password

---

## 2. Upload Code

**Option A — Git (recommended if available)**

```bash
cd ~/public_html   # or a subdomain folder
git clone https://github.com/hidooch980/molido-core1.git molido
cd molido/backend
```

**Option B — File Manager / FTP**

- Upload the project ZIP
- Extract into `public_html/molido` (or subdomain root)

Point the domain/subdomain document root to:

```
.../molido/backend/public
```

---

## 3. Configure Environment

```bash
cd ~/public_html/molido/backend
cp .env.example .env
```

Edit `.env` via File Manager or terminal:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
CACHE_STORE=file
SESSION_DRIVER=file
```

Generate key:

```bash
php artisan key:generate
```

---

## 4. Install Dependencies

Via SSH / Terminal:

```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
```

If Composer is not available in terminal, use cPanel “Setup Node.js App” / “PHP Composer” tool if provided by host, or upload `vendor/` from a local build (not ideal, but works).

---

## 5. Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

Owner should be your cPanel user.

---

## 6. .htaccess

Laravel’s default `public/.htaccess` should work on Apache:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

Ensure `mod_rewrite` is enabled (standard on cPanel).

---

## 7. Cron Job

cPanel → Cron Jobs → Add:

```
* * * * * cd /home/USERNAME/public_html/molido/backend && php artisan schedule:run >> /dev/null 2>&1
```

Replace `USERNAME` with your cPanel username.

For queue (database driver) without Supervisor, you can add:

```
*/5 * * * * cd /home/USERNAME/public_html/molido/backend && php artisan queue:work --stop-when-empty --max-time=60
```

---

## 8. SSL

cPanel → SSL/TLS Status → Run AutoSSL for your domain.

Force HTTPS in `.env`:

```
APP_URL=https://your-domain.com
```

---

## 9. Landing Page (optional)

Static 3D landing is in `/landing`.  
You can point a subdomain to `molido/landing` or copy `landing/index.html` content.

---

## 10. cPanel Limitations & Tips

| Feature | cPanel approach |
|---------|-----------------|
| Queue | Database + Cron `queue:work --stop-when-empty` |
| Cache | File / Database |
| Redis | Optional — do not require |
| Long AI jobs | Queue + timeout limits of host |
| Memory | Prefer modest AI context; external APIs only |

If the host kills long PHP processes, keep AI timeouts reasonable (`AI_TIMEOUT=30`).

---

## Checklist

- [ ] Document root → `backend/public`
- [ ] `.env` production values
- [ ] Migrations + seeders run
- [ ] Storage writable
- [ ] SSL active
- [ ] Cron configured
- [ ] Test register / login / API
