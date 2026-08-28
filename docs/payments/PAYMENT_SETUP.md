# MOLIDO — Payment Setup

## Architecture

```
PaymentService
    → PaymentProviderInterface
        → MockPaymentProvider   (default, development)
        → ZarinpalPaymentProvider
```

Only **verified** payments activate module entitlements.  
Callbacks are **idempotent** (duplicate callbacks do not double-activate).

---

## Mock (default)

```env
PAYMENT_PROVIDER=mock
```

Flow:
1. `POST /api/v1/payments/initiate` with `module_slug`
2. Open `redirect_url` → mock callback
3. Entitlement activated + invoice created

---

## Zarinpal

### 1. Account

- Register at [zarinpal.com](https://www.zarinpal.com)
- Get **Merchant ID**
- For testing use **Sandbox** merchant

### 2. Environment

```env
PAYMENT_PROVIDER=zarinpal
ZARINPAL_MERCHANT_ID=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
ZARINPAL_SANDBOX=true
APP_URL=https://your-domain.com
FRONTEND_URL=https://app.your-domain.com
```

Set `ZARINPAL_SANDBOX=false` for production.

### 3. Callback URL

Automatically built as:

```
{APP_URL}/api/v1/payments/zarinpal-callback?uuid={transaction_uuid}
```

After verify, user can be redirected to:

```
{FRONTEND_URL}/payments?payment=success|failed&uuid=...
```

### 4. Amount

Zarinpal expects **Toman** (integer).  
Store module prices in Toman in the `modules` table.

Minimum: **1000** Toman.

### 5. Test flow

1. Set provider to `zarinpal` + sandbox merchant
2. Login to Command Center → Payments
3. Select a paid module → Start payment
4. Complete sandbox payment on Zarinpal
5. Return → verify → entitlement active

---

## API

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/v1/payments/initiate` | Yes | Start payment |
| GET | `/api/v1/payments` | Yes | List transactions |
| GET | `/api/v1/payments/{uuid}` | Yes | Transaction detail |
| GET | `/api/v1/payments/mock-callback` | No | Mock return |
| GET | `/api/v1/payments/zarinpal-callback` | No | Zarinpal return |
| POST | `/api/v1/payments/verify` | No | Manual verify |

---

## Security rules

- Never trust redirect alone — always **verify** with provider
- Never store card data
- Idempotency key prevents duplicate charges/activations
- Amount must match on verify
