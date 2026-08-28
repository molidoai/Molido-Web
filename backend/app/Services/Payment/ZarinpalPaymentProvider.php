<?php

namespace App\Services\Payment;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Zarinpal Payment Provider (Iran)
 * Docs: https://docs.zarinpal.com/
 *
 * Sandbox: https://sandbox.zarinpal.com
 * Production: https://api.zarinpal.com
 */
class ZarinpalPaymentProvider implements PaymentProviderInterface
{
    protected string $merchantId;
    protected bool $sandbox;
    protected string $baseUrl;
    protected string $startPayUrl;

    public function __construct()
    {
        $this->merchantId = env('ZARINPAL_MERCHANT_ID', '');
        $this->sandbox = filter_var(env('ZARINPAL_SANDBOX', true), FILTER_VALIDATE_BOOLEAN);

        if ($this->sandbox) {
            $this->baseUrl = 'https://sandbox.zarinpal.com/pg/v4/payment';
            $this->startPayUrl = 'https://sandbox.zarinpal.com/pg/StartPay/';
        } else {
            $this->baseUrl = 'https://api.zarinpal.com/pg/v4/payment';
            $this->startPayUrl = 'https://www.zarinpal.com/pg/StartPay/';
        }
    }

    public function name(): string
    {
        return 'zarinpal';
    }

    public function initiate(PaymentTransaction $transaction): array
    {
        if (empty($this->merchantId)) {
            return [
                'success' => false,
                'message' => 'ZARINPAL_MERCHANT_ID is not configured',
            ];
        }

        // Zarinpal amount is in Toman (integer)
        $amount = (int) round((float) $transaction->amount);
        if ($amount < 1000) {
            return [
                'success' => false,
                'message' => 'Minimum amount for Zarinpal is 1000 Toman',
            ];
        }

        $callbackUrl = rtrim(env('APP_URL', 'http://localhost:8000'), '/')
            . '/api/v1/payments/zarinpal-callback?uuid=' . $transaction->uuid;

        $description = 'MOLIDO Module Payment';
        if (!empty($transaction->request_payload['module_name'])) {
            $description = 'خرید ماژول: ' . $transaction->request_payload['module_name'];
        }

        try {
            $response = Http::timeout(20)->post($this->baseUrl . '/request.json', [
                'merchant_id' => $this->merchantId,
                'amount' => $amount,
                'callback_url' => $callbackUrl,
                'description' => $description,
                'metadata' => [
                    'order_id' => $transaction->uuid,
                ],
            ]);

            $data = $response->json();
            $code = $data['data']['code'] ?? null;
            $authority = $data['data']['authority'] ?? null;

            if ($response->successful() && $code === 100 && $authority) {
                return [
                    'success' => true,
                    'provider_transaction_id' => $authority,
                    'redirect_url' => $this->startPayUrl . $authority,
                    'message' => 'Redirect to Zarinpal',
                ];
            }

            $error = $data['errors']['message'] ?? $data['errors'] ?? 'Zarinpal request failed';
            Log::warning('Zarinpal initiate failed', ['response' => $data]);

            return [
                'success' => false,
                'message' => is_string($error) ? $error : json_encode($error),
            ];
        } catch (\Throwable $e) {
            Log::error('Zarinpal initiate exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Zarinpal connection error: ' . $e->getMessage(),
            ];
        }
    }

    public function verify(PaymentTransaction $transaction, array $callbackData): array
    {
        $authority = $callbackData['Authority'] ?? $callbackData['authority'] ?? null;
        $status = $callbackData['Status'] ?? $callbackData['status'] ?? null;

        if (!$authority) {
            return [
                'success' => false,
                'message' => 'Missing Authority from Zarinpal callback',
            ];
        }

        // User cancelled on gateway
        if ($status && strtoupper((string) $status) === 'NOK') {
            return [
                'success' => false,
                'message' => 'Payment cancelled by user',
            ];
        }

        if (empty($this->merchantId)) {
            return [
                'success' => false,
                'message' => 'ZARINPAL_MERCHANT_ID is not configured',
            ];
        }

        $amount = (int) round((float) $transaction->amount);

        try {
            $response = Http::timeout(20)->post($this->baseUrl . '/verify.json', [
                'merchant_id' => $this->merchantId,
                'amount' => $amount,
                'authority' => $authority,
            ]);

            $data = $response->json();
            $code = $data['data']['code'] ?? null;
            $refId = $data['data']['ref_id'] ?? null;

            // 100 = success, 101 = already verified
            if ($response->successful() && in_array($code, [100, 101], true)) {
                return [
                    'success' => true,
                    'provider_transaction_id' => (string) ($refId ?? $authority),
                    'amount' => $amount,
                    'message' => $code === 101 ? 'Already verified' : 'Payment verified',
                    'ref_id' => $refId,
                ];
            }

            $error = $data['errors']['message'] ?? 'Verification failed';
            Log::warning('Zarinpal verify failed', ['response' => $data, 'uuid' => $transaction->uuid]);

            return [
                'success' => false,
                'message' => is_string($error) ? $error : json_encode($error),
            ];
        } catch (\Throwable $e) {
            Log::error('Zarinpal verify exception', ['message' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Zarinpal verify error: ' . $e->getMessage(),
            ];
        }
    }
}
