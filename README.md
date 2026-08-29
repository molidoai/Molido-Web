# MOLIDO CORE

**Lightweight AI-Native Business Operating Platform + embedded AI Factory**

Version: **1.0.0-alpha** (Phases 0–12 complete)

Repository: https://github.com/hidooch980/molido-core1

---

## What is MOLIDO?

MOLIDO is a lightweight, secure, modular AI-native business platform:

- Authentication & RBAC
- Customer Center (central identity)
- CRM (Leads, Deals, …)
- ERP (Products, Inventory, Orders)
- AI Chatbot + AI Gateway + Safety Gateway
- AI Workforce (8 system agents) + Task Inbox + Human Approval
- Knowledge Base + Lightweight RAG (no Vector DB required)
- Module Marketplace + Entitlements
- Internet Payment Gateway (provider-agnostic + Mock)
- Subscriptions & Trials
- Multi-tenant foundation (`organization_id`)
- cPanel + VPS ready
- **AI Factory** (inside CORE): project templates, gateway, agents/teams — see `docs/architecture/UNIFIED_CORE_FACTORY.md`

---

## Quick Start

See **[INSTALL.md](INSTALL.md)**

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
# configure DB
php artisan migrate --seed
php artisan serve
```

API: `http://localhost:8000/api/v1`

---

## Deployment

- [VPS (Nginx + Ubuntu)](docs/deployment/VPS_DEPLOYMENT.md)
- [cPanel shared hosting](docs/deployment/CPANEL_DEPLOYMENT.md)
- [Security notes](docs/security/SECURITY.md)

---

## Implemented Phases

| Phase | Description | Status |
|-------|-------------|--------|
| 0 | Project Skeleton | ✅ |
| 1 | Core + Database | ✅ |
| 2 | Auth + RBAC | ✅ |
| 3 | Customer Center | ✅ |
| 4 | CRM Foundation | ✅ |
| 5 | ERP Foundation | ✅ |
| 6 | AI Gateway + Safety | ✅ |
| 7 | Agent Router + Workforce | ✅ |
| 8 | Tasks + Human Approval | ✅ |
| 9 | Knowledge + RAG | ✅ |
| 10 | Module Marketplace | ✅ |
| 11 | Payment Gateway | ✅ |
| 12 | Subscription + Trial | ✅ |

Details: [docs/architecture/PHASES.md](docs/architecture/PHASES.md)

---

## Tech Stack

- Backend: Laravel 11 / PHP 8.3+
- Frontend: React + Vite + Tailwind (structure ready)
- DB: MySQL 8
- Queue: Database (Redis optional later)
- AI: External APIs via Gateway
- Payment: Plugin interface + Mock provider

---

## Principles

1. Do not over-engineer  
2. AI Chatbot & Workforce from day one  
3. Payment verified before activation  
4. Human Approval for sensitive actions  
5. Tenant isolation mandatory  
6. cPanel compatibility preserved  
7. No mandatory Redis / Docker / K8s / Vector DB  

---

## License

Proprietary — All rights reserved.
