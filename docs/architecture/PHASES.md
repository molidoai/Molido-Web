# MOLIDO CORE — Implementation Phases

## Phase 0–7 ✅

## Phase 8 — AI Task System + Human Approval ✅
- Tables: ai_tasks, approvals
- Models: AiTask, Approval
- TaskController (Inbox):
  - List / create / show / update status
  - Statuses: pending, working, waiting_approval, completed, failed, cancelled
- ApprovalController:
  - List pending approvals
  - Create approval request
  - Review (approve / reject)
- Risk levels + expiry (24h)
- Linked task status updates
- Permissions: ai.agent.execute, ai.agent.approve
- API:
  - /api/v1/ai/tasks
  - /api/v1/ai/approvals

## Phase 9 — Knowledge Base + Lightweight RAG (Next)
## Phase 10+ — Marketplace, Payment, Subscription...

**Rule:** Never mark a phase DONE until tests + security checks pass.
