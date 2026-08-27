<?php

namespace App\Services\Payment;

use App\Models\PaymentTransaction;
use Illuminate\Support\Str;

/**
 * Mock / Sandbox payment provider for development and testing.
 * Simulates redirect + successful verification.
 */
class MockPaymentProvider implements PaymentProviderInterface
{
    public function name(): string
    {
        return 'mock';
    }

    public function initiate(PaymentTransaction $transaction): array
    {
        $token = 'mock_' . Str::random(24);

        return [
            'success' => true,
            'provider_transaction_id' => $token,
            'redirect_url' => url('/api/v1/payments/mock-callback?uuid=' . $transaction->uuid . '&token=' . $token . '&status=ok'),
            'message' => 'Redirect to mock payment page',
        ];
    }

    public function verify(PaymentTransaction $transaction, array $callbackData): array
    {
        $status = $callbackData['status'] ?? 'failed';
        $token = $callbackData['token'] ?? null;

        if ($status === 'ok' && $token && str_starts_with($token, 'mock_')) {
            return [
                'success' => true,
                'provider_transaction_id' => $token,
                'amount' => $transaction->amount,
                'message' => 'Mock payment verified',
            ];
        }

        return [
            'success' => false,
            'message' => 'Mock payment verification failed',
        ];
    }
}
