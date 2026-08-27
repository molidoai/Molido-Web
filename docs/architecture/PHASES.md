# MOLIDO CORE — Implementation Phases

## Phase 0–10 ✅

## Phase 11 — Internet Payment Gateway ✅
- Tables: payment_transactions, invoices
- Models: PaymentTransaction, Invoice
- PaymentProviderInterface (provider-agnostic)
- MockPaymentProvider (sandbox)
- PaymentService:
  - initiate module payment
  - verify + activate entitlement (idempotent)
  - amount check
  - invoice creation on success
- Only verified payment activates module
- Duplicate callbacks are safe
- API:
  - POST /api/v1/payments/initiate
  - GET  /api/v1/payments
  - GET  /api/v1/payments/{uuid}
  - GET  /api/v1/payments/mock-callback (dev)
  - POST /api/v1/payments/verify

## Phase 12 — Subscription + Trial polish (Next)
## Phase 13+ — Security, Audit polish, Deployment docs, Frontend...

**Rule:** Never mark a phase DONE until tests + security checks pass.
