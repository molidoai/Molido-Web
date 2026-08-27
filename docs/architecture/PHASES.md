# MOLIDO CORE — Implementation Phases

## Phase 0–6 ✅

## Phase 7 — Agent Router + AI Workforce Foundation ✅
- AgentRouter service (intent-based + explicit slug routing)
- AiAgent model
- AiAgentSeeder with 8 system agents:
  - General, Sales, Support, CRM, ERP, Marketing, Finance, Technical
- AgentController (list + show)
- ChatController now resolves agent and uses agent system prompt
- API:
  - GET /api/v1/ai/agents
  - GET /api/v1/ai/agents/{slug}

## Phase 8 — AI Task System + Human Approval (Next)
## Phase 9+ — Marketplace, Payment, etc.

**Rule:** Never mark a phase DONE until tests + security checks pass.
