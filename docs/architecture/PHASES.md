# MOLIDO CORE — Implementation Phases

## Phase 0–5 ✅

## Phase 6 — AI Gateway + Safety ✅
- AIGateway service (provider-agnostic, OpenAI-compatible)
- AISafetyGateway (prompt injection, action mode, sensitive keywords)
- Tables: ai_conversations, ai_messages, ai_usages, ai_agents
- Models: AiConversation, AiMessage, AiUsage
- ChatController:
  - List / create conversations
  - Get messages
  - Send message → Gateway → Safety → Response
- Cost / token tracking
- Mock mode when AI_API_KEY is missing
- Permission: ai.chat.use

## Phase 7 — Chatbot polish + Agent Router (Next)
## Phase 8+ — Workforce, Tasks, Marketplace, Payment...

**Rule:** Never mark a phase DONE until tests + security checks pass.
