# MOLIDO CORE — Implementation Phases

## Phase 0–11 ✅

## Phase 12 — Subscription + Trial ✅
- Tables: plans, subscriptions
- Models: Plan, Subscription
- SubscriptionService: subscribe, cancel, processExpirations
- Trial days configurable per plan
- Grace period (default 3 days)
- Statuses: trialing, active, past_due, cancelled, expired, suspended
- Sync with Entitlements
- PlanSeeder linked to modules
- API:
  - GET  /api/v1/subscriptions/plans
  - GET  /api/v1/subscriptions
  - POST /api/v1/subscriptions/subscribe
  - POST /api/v1/subscriptions/{id}/cancel

## Next recommended
- Phase 13: Audit polish + Feature Flags API
- Phase 14: Frontend Command Center (React)
- Phase 15: Deployment docs (VPS + cPanel)
- Full test suite

**Rule:** Never mark a phase DONE until tests + security checks pass.
