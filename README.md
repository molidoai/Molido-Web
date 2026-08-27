# MOLIDO CORE

**Lightweight AI-Native Business Operating Platform**

Version: 1.0.0-skeleton (Phase 0 + Phase 1)

---

## What is MOLIDO?

MOLIDO is a lightweight, secure, modular and scalable AI-native business platform that includes:

- Authentication & RBAC
- Customer Center
- CRM Foundation
- ERP Foundation
- AI Chatbot
- AI Gateway + Safety Gateway
- AI Workforce (Virtual Employees)
- Knowledge Base (Lightweight RAG)
- Module Marketplace
- Internet Payment Gateway
- Orders, Subscriptions, Trials, Entitlements, Invoices
- Multi-tenant foundation
- Multi-language (Persian + English)
- cPanel + VPS ready

---

## Architecture

```
LIGHTWEIGHT MODULAR MONOLITH

Internet → Nginx/Apache → Laravel → MySQL → Database Queue → External AI APIs → External Payment Providers
```

Future-ready for gradual scaling (Redis, dedicated workers, microservices when needed).

---

## Technology Stack

| Layer        | Technology              |
|--------------|-------------------------|
| Backend      | Laravel 11 (PHP 8.3+)   |
| Frontend     | React 18 + Vite + Tailwind |
| Database     | MySQL 8                 |
| Queue        | Database Queue (Redis optional) |
| Cache        | File / Database         |
| Auth         | Laravel Sanctum / Session |
| AI           | Provider-agnostic Gateway |

---

## Project Structure

```
MOLIDO/
├── backend/          # Laravel application
├── frontend/         # React + Vite application
├── docs/             # Documentation
├── scripts/          # Installation & deployment scripts
└── README.md
```

---

## Current Progress

### Phase 0 — Project Skeleton ✅
- Full directory structure
- Core configuration files
- Documentation foundation
- Installation guides

### Phase 1 — Core + Database ✅
- Core models (User, Organization, Customer, Role, Permission...)
- Migrations for core tables
- Multi-tenant foundation (`organization_id`)
- Basic service interfaces

### Next Phases (to be implemented)
- Phase 2: Authentication + RBAC
- Phase 3: Customer Center
- Phase 4: CRM
- ... (see original specification)

---

## Quick Start (Development)

### Requirements
- PHP 8.3+
- Composer
- Node.js 20+
- MySQL 8
- Git

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# Configure DB credentials in .env
php artisan migrate
php artisan db:seed
php artisan serve
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

---

## Deployment

See detailed guides in `/docs`:

- `docs/deployment/VPS_DEPLOYMENT.md`
- `docs/deployment/CPANEL_DEPLOYMENT.md`
- `docs/security/SECURITY.md`

---

## Important Principles

1. Do not over-engineer
2. AI Chatbot & AI Workforce from day one
3. Payment must be verified before activation
4. Human Approval for sensitive actions
5. Full Audit trail
6. Tenant isolation is mandatory
7. cPanel compatibility must be preserved
8. No mandatory Redis / Docker / Kubernetes / Vector DB

---

## License

Proprietary — All rights reserved.
