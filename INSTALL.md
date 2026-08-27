# MOLIDO CORE — Quick Install

## Requirements

- PHP 8.3+
- Composer
- MySQL 8
- Node.js 20+ (for frontend build, optional)
- Git

## Development Setup

```bash
git clone https://github.com/hidooch980/molido-core1.git
cd molido-core1/backend

composer install
cp .env.example .env
php artisan key:generate

# Configure DB in .env
php artisan migrate
php artisan db:seed

php artisan serve
```

API base: `http://localhost:8000/api/v1`

### Frontend (optional)

```bash
cd frontend
npm install
npm run dev
```

### Landing (3D)

Open `landing/index.html` in browser or serve the folder.

## Production

See:

- [docs/deployment/VPS_DEPLOYMENT.md](docs/deployment/VPS_DEPLOYMENT.md)
- [docs/deployment/CPANEL_DEPLOYMENT.md](docs/deployment/CPANEL_DEPLOYMENT.md)

## Default Seeded Data

After `db:seed`:

- Roles & permissions (Super Admin, Admin, Manager, Sales, Support, ...)
- 8 AI Agents (General, Sales, Support, CRM, ERP, ...)
- Sample modules (CRM Pro, ERP Lite, AI Workforce, ...)
- Sample plans (monthly/yearly)

First registered user becomes **Admin** of a new Organization (14-day trial on org).

## Frontend (Command Center)

```bash
cd frontend
npm install
npm run dev
```

Open http://localhost:5173

Proxy to API is configured for `/api` → `http://localhost:8000`.

For production:

```bash
npm run build
# serve dist/ or deploy behind same domain
```
