# MOLIDO CORE — Implementation Phases

## Phase 0 — Project Skeleton ✅
## Phase 1 — Core + Database ✅
## Phase 2 — Authentication + RBAC ✅
## Phase 3 — Customer Center ✅

## Phase 4 — CRM Foundation ✅
- Migration: contacts, leads, deals, crm_tasks, tickets, notes, activities
- Models: Lead, Deal
- Controllers: LeadController, DealController (full CRUD)
- Tenant isolation on all CRM entities
- API endpoints:
  - /api/v1/crm/leads
  - /api/v1/crm/deals
- Permission protected
- Linked to central Customer identity

## Phase 5 — ERP Foundation (Next)
- Products, Inventory, Orders, Expenses

## Phase 6 — AI Gateway + Safety
## Phase 7 — Chatbot
## Phase 8–25 — Remaining modules

**Rule:** Never mark a phase DONE until tests + security checks pass.
