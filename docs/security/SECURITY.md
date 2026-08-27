# MOLIDO CORE — Security Notes

## Principles

- Never run the app as root
- Never commit secrets (`.env` is gitignored)
- Never store raw card data
- Payment activation only after verified callback
- Payment callbacks must be idempotent
- AI actions respect RBAC and Safety Gateway
- Sensitive AI actions require Human Approval
- Tenant isolation via `organization_id` on all business data

## Authentication

- Passwords hashed (Laravel Hash / bcrypt)
- API tokens via Laravel Sanctum
- Login rate limiting
- Session / token revocation on logout

## Authorization

- RBAC with explicit permissions
- Middleware `permission:xxx`
- AI tools must not bypass permission checks

## AI Safety

- All requests through AI Gateway
- Safety Gateway checks prompt injection patterns
- Action mode requires auth context
- Sensitive keywords can force approval flow
- Token usage tracked per organization

## Data

- Soft deletes where appropriate
- Audit log foundation (`audit_logs` table)
- Minimize sensitive fields in logs

## Headers & Transport

- HTTPS in production
- Security headers in Nginx config (see VPS guide)
- CSRF for web routes (API uses tokens)

## Deployment

- Firewall (SSH + HTTP/HTTPS only)
- MySQL bound to localhost
- File permissions: storage/bootstrap writable by web user only
- Disable directory listing

## Reporting

If you discover a vulnerability, do not open a public issue with exploit details. Contact the maintainer privately.
