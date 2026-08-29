# Local completeness checklist (before VPS access)

## Product surface

- [x] Auth register/login/logout/me
- [x] Password forgot/reset
- [x] Customers CRM identity
- [x] Leads + convert to deal
- [x] Deals pipeline UI
- [x] Products + Orders ERP
- [x] AI Chat + agents + teams
- [x] Virtual employees (workforce)
- [x] AI Factory projects
- [x] Knowledge base UI
- [x] Tasks + Approvals
- [x] Marketplace modules + payments + subscriptions
- [x] Team invites + notifications
- [x] Feature flags + audit + dashboard stats
- [x] Health endpoint
- [x] Org AI token budget gate

## Docs

- [x] UNIFIED_CORE_FACTORY.md
- [x] VPS / cPanel deployment guides
- [x] RESOURCE_BUDGET_6GB.md
- [x] EMAIL_SETUP.md
- [x] PAYMENT_SETUP.md

## Still requires real VPS (cannot fake)

- [ ] Phase 0 server audit on 194.5.179.50
- [ ] DNS molido.ir → IP
- [ ] TLS certificates
- [ ] Production .env secrets
- [ ] migrate --seed on production DB
- [ ] Real SMTP + Zarinpal merchant
- [ ] Measured RAM/CPU under load

## Definition of "code complete" for handoff

Repository builds the modular monolith; production acceptance only after VPS Phase 0–1 evidence.
