# MOLIDO CORE — Implementation Phases

## Phase 0–9 ✅

## Phase 10 — Module Marketplace ✅
- Tables: modules, entitlements
- Models: Module, Entitlement
- ModuleController: list, show, activate (free/trial), myModules
- ModuleSeeder with sample modules (CRM Pro, ERP Lite, AI Workforce...)
- Entitlement status: active, trial, expired, suspended, cancelled
- Paid modules return 402 until Payment phase
- API:
  - GET  /api/v1/modules
  - GET  /api/v1/modules/my
  - GET  /api/v1/modules/{slug}
  - POST /api/v1/modules/{slug}/activate

## Phase 11 — Internet Payment Gateway (Next)
## Phase 12+ — Orders, Subscription, Invoice, Security hardening...

**Rule:** Never mark a phase DONE until tests + security checks pass.
