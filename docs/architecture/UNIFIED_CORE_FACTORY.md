# MOLIDO — CORE + AI FACTORY (Unified)

## Decision

**One modular monolith. One codebase. One deploy on the VPS.**

| Brand / Layer | Role |
|---------------|------|
| **MOLIDO CORE** | Business OS: Auth, CRM, ERP, Billing, Tenancy, UI |
| **MOLIDO AI FACTORY** | Engineering engine *inside* CORE: Gateway, Agents, Teams, RAG, Projects, Evaluation hooks |

They are **not** two stacks (no second Laravel app, no forced greenfield Postgres migration for day one).

```
Internet → Nginx → MOLIDO (Laravel modular monolith)
                      │
         ┌────────────┼────────────┐
         │            │            │
      CORE APIs    AI FACTORY    Data
      CRM/ERP      Gateway       MySQL (now)
      Team/Billing Agents/Teams  Redis optional
      Marketplace  RAG/Projects  External LLM APIs
```

## Resource policy (3 vCPU / 6 GB)

- No local large LLM / no GPU
- No Kubernetes / Elasticsearch
- Optional Redis only if RAM allows after audit
- Database queue (default) before Redis queue
- External inference only (OpenAI-compatible, etc.)
- Target RAM headroom: keep normal usage ≤ 70%

## Mapping Factory concepts → existing CORE

| AI Factory idea | Already in CORE / next module |
|-----------------|-------------------------------|
| AI Gateway | `AIGateway` + Safety Gateway |
| Provider independence | Provider env + adapters |
| Agents | `AiAgent` + Workforce UI |
| AI Teams | `AiTeam` + routing |
| Human approval | `Approval` + Approvals UI |
| RAG light | Knowledge articles + MySQL fulltext |
| Audit | `AuditLog` + Audit UI |
| RBAC | Roles / Permissions |
| Usage / cost hooks | `AiUsage` |
| Project Factory | **`factory_projects`** (new, inside CORE) |
| Agent Runtime versioning | agent fields + future `version` column |
| Tool registry | tools JSON on agents + future `ai_tools` table |

## Phase gates (unified)

| Phase | Focus | Evidence |
|-------|--------|----------|
| 0 | Server read-only audit | `docs/server-audit.md` on real VPS |
| 1 | CORE stable on VPS | HTTPS, migrate, login, health |
| 2 | Factory Projects module | CRUD projects + link to agents/teams |
| 3 | Harden Gateway (limits, budget) | usage caps per org |
| 4 | RAG improvements | still MySQL-first |
| 5 | Optional Redis / Postgres path | only after audit + RAM budget |

## Explicit non-goals (now)

- Second application server process for “factory only”
- Automatic OS upgrade / firewall rewrite without audit
- Fake “DONE” without health/tests on target environment

## Product story

Customer buys **MOLIDO** → runs business modules **and** builds AI projects/agents from the same panel (**AI Factory** section).
