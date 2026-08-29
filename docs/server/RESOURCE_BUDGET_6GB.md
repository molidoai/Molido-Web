# Resource budget — 3 vCPU / 6 GB RAM (MOLIDO unified)

## Target allocation (approximate)

| Component | RAM | Notes |
|-----------|-----|--------|
| OS + SSH | 0.5–0.8 GB | keep headroom |
| PHP-FPM (2–3 workers) | 0.4–0.8 GB | limit pm.max_children |
| Nginx | 0.05 GB | |
| MySQL | 0.8–1.5 GB | innodb_buffer_pool ~512M–768M |
| Redis (optional) | 0–128 MB | maxmemory + allkeys-lru |
| Queue worker (1) | 0.1–0.2 GB | database queue default |
| Node build | temporary | build on CI or local, not always on VPS |
| **Free / cache** | ≥ 1.5 GB | never plan 100% usage |

Normal total app stack goal: **≤ 70% of 6 GB (~4.2 GB)**.

## Do not run on this box

- Local large LLM
- Elasticsearch / OpenSearch
- Kubernetes
- Multiple heavy workers
- Chromium browsers for scraping at scale

## AI

All inference = **external API** via AI Gateway.  
Token budget: `AI_ORG_MONTHLY_TOKEN_BUDGET` or org settings.
