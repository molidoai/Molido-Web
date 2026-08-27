<?php

namespace App\Services\Payment;

use App\Models\PaymentTransaction;

interface PaymentProviderInterface
{
    /**
     * Initiate payment and return redirect URL or payment token.
     */
    public function initiate(PaymentTransaction $transaction): array;

    /**
     * Verify callback / return from provider.
     * Must be idempotent-safe.
     */
    public function verify(PaymentTransaction $transaction, array $callbackData): array;

    /**
     * Provider name.
     */
    public function name(): string;
}
