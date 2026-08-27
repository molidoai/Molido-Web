# MOLIDO CORE — Implementation Phases

## Phase 0 — Project Skeleton ✅
- Directory structure
- README + documentation foundation
- composer.json / package.json
- .env.example
- .gitignore

## Phase 1 — Core + Database ✅
- Organization (multi-tenant root)
- User model
- Role & Permission models
- Customer (central identity)
- Feature Flags table
- Audit Logs table
- Core migration

## Phase 2 — Authentication + RBAC (Next)
- Registration / Login / Logout
- Password reset
- Sanctum API tokens
- Role-based middleware
- Permission checks
- Seed default roles & permissions

## Phase 3 — Customer Center
- Central customer CRUD
- Link to User / Organization
- Integration points for CRM / Chat / Orders

## Phase 4 — CRM Foundation
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
