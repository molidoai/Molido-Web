# MOLIDO CORE — Implementation Phases

## Phase 0–8 ✅

## Phase 9 — Knowledge Base + Lightweight RAG ✅
- Table: knowledge_articles
- Model: KnowledgeArticle
- KnowledgeSearch service (MySQL LIKE-based relevance scoring)
- KnowledgeController: CRUD + search + retrieveForAI
- No Vector DB required (interface ready for future)
- System-wide + organization knowledge
- API:
  - /api/v1/knowledge
  - /api/v1/knowledge/search
  - /api/v1/knowledge/retrieve

## Phase 10 — Module Marketplace (Next)
## Phase 11+ — Payment, Orders, Subscription, Entitlement...

**Rule:** Never mark a phase DONE until tests + security checks pass.
