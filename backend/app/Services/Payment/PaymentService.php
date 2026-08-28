<?php

namespace App\Services\Payment;

use App\Models\Entitlement;
use App\Models\Invoice;
use App\Models\Module;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    public function getProvider(?string $name = null): PaymentProviderInterface
    {
        $name = $name ?: env('PAYMENT_PROVIDER', 'mock');

        return match ($name) {
            'zarinpal' => new ZarinpalPaymentProvider(),
            'mock' => new MockPaymentProvider(),
            default => new MockPaymentProvider(),
        };
    }

    /**
     * Create transaction and initiate payment for a module.
     */
    public function initiateModulePayment(
        int $organizationId,
        int $userId,
        Module $module,
        ?int $customerId = null,
        ?string $idempotencyKey = null
    ): array {
        // Idempotency: return existing pending/paid if same key
        if ($idempotencyKey) {
            $existing = PaymentTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return [
                    'transaction' => $existing,
                    'already_exists' => true,
                ];
            }
        }

        $provider = $this->getProvider();

        $transaction = PaymentTransaction::create([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'customer_id' => $customerId,
            'module_id' => $module->id,
            'amount' => $module->price,
            'currency' => $module->currency ?? 'IRR',
            'provider' => $provider->name(),
            'status' => 'pending',
            'idempotency_key' => $idempotencyKey ?: (string) Str::uuid(),
            'request_payload' => [
                'module_slug' => $module->slug,
                'module_name' => $module->name,
            ],
        ]);

        $result = $provider->initiate($transaction);

        if ($result['success'] ?? false) {
            $transaction->update([
                'provider_transaction_id' => $result['provider_transaction_id'] ?? null,
                'status' => 'redirected',
                'redirected_at' => now(),
            ]);
        } else {
            $transaction->update([
                'status' => 'failed',
                'failure_reason' => $result['message'] ?? 'Initiation failed',
            ]);
        }

        return [
            'transaction' => $transaction->fresh(),
            'redirect_url' => $result['redirect_url'] ?? null,
            'provider_result' => $result,
        ];
    }

    /**
     * Verify payment callback — must be idempotent.
     * Only verified payment activates entitlement.
     */
    public function verifyAndActivate(string $uuid, array $callbackData): array
    {
        return DB::transaction(function () use ($uuid, $callbackData) {
            $transaction = PaymentTransaction::where('uuid', $uuid)->lockForUpdate()->first();

            if (!$transaction) {
                return ['success' => false, 'message' => 'Transaction not found'];
            }

            // Already paid — idempotent success
            if ($transaction->status === 'paid') {
                return [
                    'success' => true,
                    'message' => 'Already verified',
                    'transaction' => $transaction,
                    'idempotent' => true,
                ];
            }

            if (!in_array($transaction->status, ['pending', 'redirected'])) {
                return [
                    'success' => false,
                    'message' => 'Invalid transaction status: ' . $transaction->status,
                ];
            }

            $provider = $this->getProvider($transaction->provider);
            $verify = $provider->verify($transaction, $callbackData);

            $transaction->update([
                'callback_payload' => $callbackData,
            ]);

            if (!($verify['success'] ?? false)) {
                $transaction->update([
                    'status' => 'failed',
                    'failure_reason' => $verify['message'] ?? 'Verification failed',
                ]);
                return [
                    'success' => false,
                    'message' => $verify['message'] ?? 'Verification failed',
                    'transaction' => $transaction->fresh(),
                ];
            }

            // Optional amount check
            if (isset($verify['amount']) && (float) $verify['amount'] != (float) $transaction->amount) {
                $transaction->update([
                    'status' => 'failed',
                    'failure_reason' => 'Amount mismatch',
                ]);
                return ['success' => false, 'message' => 'Amount mismatch'];
            }

            $transaction->update([
                'status' => 'paid',
                'provider_transaction_id' => $verify['provider_transaction_id'] ?? $transaction->provider_transaction_id,
                'verified_at' => now(),
                'paid_at' => now(),
            ]);

            // Activate entitlement for module
            if ($transaction->module_id) {
                $module = Module::find($transaction->module_id);
                Entitlement::updateOrCreate(
                    [
                        'organization_id' => $transaction->organization_id,
                        'module_id' => $transaction->module_id,
                    ],
                    [
                        'status' => 'active',
                        'source' => 'purchase',
                        'starts_at' => now(),
                        'ends_at' => $module && $module->billing_type === 'monthly'
                            ? now()->addMonth()
                            : ($module && $module->billing_type === 'yearly' ? now()->addYear() : null),
                    ]
                );
            }

            // Create simple invoice
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . strtoupper(Str::random(8)),
                'organization_id' => $transaction->organization_id,
                'customer_id' => $transaction->customer_id,
                'payment_transaction_id' => $transaction->id,
                'subtotal' => $transaction->amount,
                'total' => $transaction->amount,
                'currency' => $transaction->currency,
                'status' => 'paid',
                'items' => $transaction->request_payload,
                'issued_at' => now(),
                'paid_at' => now(),
            ]);

            return [
                'success' => true,
                'message' => 'Payment verified and module activated',
                'transaction' => $transaction->fresh(),
                'invoice' => $invoice,
            ];
        });
    }
}
