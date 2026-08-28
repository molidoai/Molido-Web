# MOLIDO — Testing

## Run unit tests

```bash
cd backend
composer install
./vendor/bin/phpunit --testsuite Unit
```

## Unit tests included

- `AISafetyGatewayTest` — injection, empty input, approval requirements
- `AgentRouterTest` — system prompt rules

## Feature tests

Require full Laravel bootstrap (`CreatesApplication`).  
After standard Laravel install, implement:

1. Auth register/login
2. Tenant isolation on customers
3. Payment idempotency
4. RBAC 403 on missing permission

```bash
php artisan test
```
