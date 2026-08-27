# MOLIDO CORE — Implementation Phases

## Phase 0 — Project Skeleton ✅
- Directory structure
- README + documentation foundation
- composer.json / package.json
- .env.example
- .gitignore
- 3D Landing page

## Phase 1 — Core + Database ✅
- Organization (multi-tenant root)
- User model
- Role & Permission models
- Customer (central identity)
- Feature Flags table
- Audit Logs table
- Core migration

## Phase 2 — Authentication + RBAC ✅
- Registration (creates Organization + Admin user)
- Login with Rate Limiting
- Logout
- Current user profile (`/me`)
- Laravel Sanctum token authentication
- CheckPermission middleware
- RolePermissionSeeder with all initial roles & permissions
- Default trial period (14 days)

## Phase 3 — Customer Center ✅
- Central Customer identity (no duplicates across modules)
- Full CRUD with organization isolation
- Search & filter
- Permission-protected endpoints:
  - GET    /api/v1/customers
  - POST   /api/v1/customers
  - GET    /api/v1/customers/{id}
  - PUT    /api/v1/customers/{id}
  - DELETE /api/v1/customers/{id}
- Tenant isolation enforced (organization_id)

## Phase 4 — CRM Foundation (Next)
- Leads, Deals, Contacts, Activities, Notes, Tickets

## Phase 5 — ERP Foundation
- Products, Inventory, Orders, Expenses

## Phase 6 — AI Gateway + Safety
- AI Gateway service
- Model Router
- Safety Gateway
- Cost/Token tracking

## Phase 7 — Chatbot
- Conversation history
- Context management
- Tool calling foundation

## Phase 8–12 — Agents, Workforce, Tasks, Approval, Knowledge

## Phase 13–17 — Marketplace, Payment, Orders, Subscription, Invoice

## Phase 18–25 — Security hardening, Tests, Deployment, Performance

---

**Rule:** Never mark a phase DONE until tests + security checks pass.
