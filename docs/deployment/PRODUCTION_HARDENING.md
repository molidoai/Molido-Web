# MOLIDO — Production Hardening Checklist

## Application

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Strong unique `APP_KEY`
- [ ] `APP_URL` is HTTPS
- [ ] `FRONTEND_URL` set
- [ ] Config/route/view cached: `php artisan config:cache && route:cache && view:cache`
- [ ] `composer install --no-dev --optimize-autoloader`

## Secrets

- [ ] `.env` not in git
- [ ] AI_API_KEY only in env
- [ ] ZARINPAL_MERCHANT_ID only in env
- [ ] DB password strong
- [ ] Sanctum/session secure cookies over HTTPS

## Database

- [ ] MySQL bound to localhost
- [ ] Migrations applied
- [ ] Seeders run once
- [ ] Automated backups (daily)
- [ ] Restore tested

## Web server

- [ ] HTTPS (Let's Encrypt / AutoSSL)
- [ ] Security headers (X-Frame-Options, X-Content-Type-Options)
- [ ] Document root = `backend/public`
- [ ] Directory listing disabled

## Process

- [ ] Queue worker (Supervisor or cron)
- [ ] Scheduler cron `* * * * *`
- [ ] App not running as root
- [ ] storage + bootstrap/cache writable by web user only

## Payments

- [ ] `PAYMENT_PROVIDER=zarinpal` (or chosen PSP)
- [ ] `ZARINPAL_SANDBOX=false` in production
- [ ] Callback URL reachable publicly
- [ ] Test one real small payment + verify entitlement

## AI

- [ ] Rate limits / org cost limits reviewed
- [ ] Safety Gateway enabled (always on)
- [ ] Human approval for financial actions

## Monitoring

- [ ] Log rotation for `storage/logs`
- [ ] Disk space alerts
- [ ] Uptime check on `/api/v1` health (add if needed)
