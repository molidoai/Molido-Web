# MOLIDO CORE — Implementation Phases

## Completed ✅

| Phase | Title |
|-------|--------|
| 0–12 | Backend core |
| 13 | Deployment docs |
| 14–15 | React Command Center |
| 16 | Zarinpal Payment Provider |
| **17** | **Feature Flags API + Admin UI** |

## Feature Flags

Keys (seeded):
- CHATBOT_ENABLED
- AI_WORKFORCE_ENABLED
- CRM_ENABLED / ERP_ENABLED
- MARKETPLACE_ENABLED / PAYMENT_ENABLED / SUBSCRIPTION_ENABLED
- VOICE_ENABLED / ADVANCED_RAG_ENABLED (off by default)

API:
- GET  /api/v1/feature-flags
- GET  /api/v1/feature-flags/enabled
- PUT  /api/v1/feature-flags/{key}
- GET  /api/v1/feature-flags/check/{key}

UI: `/feature-flags` in Command Center

## Optional next

- Analytics dashboard charts
- Automated test suite
- Audit log UI
