# MOLIDO CORE — VPS Deployment Guide

## Recommended Server

- Ubuntu 24.04 LTS
- 4 vCPU / 8 GB RAM / 80–100 GB NVMe (minimum: 2 vCPU / 4 GB)
- Nginx
- PHP 8.3+
- MySQL 8
- SSL (Let's Encrypt)
- No GPU required

---

## 1. Initial Server Setup

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server git curl unzip software-properties-common
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-xml php8.3-mbstring \
  php8.3-curl php8.3-zip php8.3-bcmath php8.3-gd php8.3-intl
```

Install Composer:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

Install Node.js (for frontend build):

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
```

---

## 2. Clone & Configure

```bash
cd /var/www
sudo git clone https://github.com/hidooch980/molido-core1.git molido
sudo chown -R www-data:www-data molido
cd molido/backend
```

```bash
cp .env.example .env
php artisan key:generate
# Edit .env: DB_*, APP_URL, AI_API_KEY, PAYMENT_PROVIDER, etc.
```

```bash
composer install --optimize-autoloader --no-dev
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Storage & permissions:

```bash
php artisan storage:link
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 3. Frontend Build (optional)

```bash
cd /var/www/molido/frontend
npm install
npm run build
# Serve dist/ via Nginx or copy to public/
```

Landing page is already static at `/landing`.

---

## 4. Nginx Config

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/molido/backend/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable & reload:

```bash
sudo ln -s /etc/nginx/sites-available/molido /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

SSL:

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d your-domain.com
```

---

## 5. Queue & Scheduler

```bash
# Supervisor for queue (database driver)
sudo apt install supervisor -y
```

`/etc/supervisor/conf.d/molido-worker.conf`:

```ini
[program:molido-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/molido/backend/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/molido/backend/storage/logs/worker.log
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start molido-worker:*
```

Cron (Laravel Scheduler):

```bash
sudo crontab -e -u www-data
```

Add:

```
* * * * * cd /var/www/molido/backend && php artisan schedule:run >> /dev/null 2>&1
```

---

## 6. Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

Do **not** expose MySQL publicly. Bind to `127.0.0.1`.

---

## 7. Production Checklist

- [ ] `APP_ENV=production` / `APP_DEBUG=false`
- [ ] Strong `APP_KEY`
- [ ] HTTPS only
- [ ] DB credentials secured
- [ ] AI and Payment keys in `.env` only
- [ ] Queue worker running
- [ ] Cron running
- [ ] Backups configured
- [ ] File permissions correct (not root)
