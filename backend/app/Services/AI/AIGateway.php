<?php

namespace App\Services\AI;

use App\Models\AiUsage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MOLIDO AI Gateway
 * All AI requests must pass through this gateway.
 * Provider-agnostic, with cost control and logging.
 */
class AIGateway
{
    protected string $provider;
    protected string $apiKey;
    protected string $model;
    protected int $maxTokens;
    protected int $timeout;

    public function __construct()
    {
        $this->provider = config('services.ai.provider', env('AI_PROVIDER', 'openai'));
        $this->apiKey = config('services.ai.api_key', env('AI_API_KEY', ''));
        $this->model = config('services.ai.model', env('AI_MODEL', 'gpt-4o-mini'));
        $this->maxTokens = (int) env('AI_MAX_TOKENS', 4096);
        $this->timeout = (int) env('AI_TIMEOUT', 30);
    }

    /**
     * Send a chat completion request through the gateway.
     */
    public function chat(array $messages, array $options = []): array
    {
        $organizationId = $options['organization_id'] ?? null;
        $userId = $options['user_id'] ?? null;
        $agent = $options['agent'] ?? 'general';

        // Safety pre-check
        $safety = app(AISafetyGateway::class);
        $safetyResult = $safety->validate($messages, $options);

        if (!$safetyResult['allowed']) {
            return [
                'success' => false,
                'error' => $safetyResult['reason'] ?? 'Blocked by safety gateway',
                'code' => 'safety_blocked',
            ];
        }

        try {
            $response = $this->callProvider($messages, $options);

            // Track usage
            $this->trackUsage([
                'organization_id' => $organizationId,
                'user_id' => $userId,
                'provider' => $this->provider,
                'model' => $options['model'] ?? $this->model,
                'agent' => $agent,
                'input_tokens' => $response['usage']['input_tokens'] ?? 0,
                'output_tokens' => $response['usage']['output_tokens'] ?? 0,
                'request_type' => 'chat',
            ]);

            return [
                'success' => true,
                'content' => $response['content'] ?? '',
                'usage' => $response['usage'] ?? [],
                'model' => $response['model'] ?? $this->model,
            ];
        } catch (\Throwable $e) {
            Log::error('AI Gateway error', [
                'message' => $e->getMessage(),
                'provider' => $this->provider,
            ]);

            return [
                'success' => false,
                'error' => 'AI service temporarily unavailable',
                'code' => 'provider_error',
            ];
        }
    }

    /**
     * Provider adapter (OpenAI-compatible for now).
     * Can be extended for other providers.
     */
    protected function callProvider(array $messages, array $options): array
    {
        if (empty($this->apiKey)) {
            // Fallback mock for development without API key
            return [
                'content' => '[AI Mock] پاسخ آزمایشی — لطفاً AI_API_KEY را در .env تنظیم کنید.',
                'usage' => ['input_tokens' => 10, 'output_tokens' => 20],
                'model' => 'mock',
            ];
        }

        $model = $options['model'] ?? $this->model;

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'temperature' => $options['temperature'] ?? 0.7,
        ];

        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->post('https://api.openai.com/v1/chat/completions', $payload);

        if (!$response->successful()) {
            throw new \RuntimeException('Provider returned error: ' . $response->body());
        }

        $data = $response->json();

        return [
            'content' => $data['choices'][0]['message']['content'] ?? '',
            'usage' => [
                'input_tokens' => $data['usage']['prompt_tokens'] ?? 0,
                'output_tokens' => $data['usage']['completion_tokens'] ?? 0,
            ],
            'model' => $data['model'] ?? $model,
        ];
    }

    protected function trackUsage(array $data): void
    {
        if (empty($data['organization_id'])) {
            return;
        }

        // Simple cost estimate (example rates)
        $inputCost = ($data['input_tokens'] / 1000) * 0.00015;
        $outputCost = ($data['output_tokens'] / 1000) * 0.0006;

        AiUsage::create([
            'organization_id' => $data['organization_id'],
            'user_id' => $data['user_id'] ?? null,
            'provider' => $data['provider'] ?? $this->provider,
            'model' => $data['model'] ?? $this->model,
            'agent' => $data['agent'] ?? null,
            'input_tokens' => $data['input_tokens'] ?? 0,
            'output_tokens' => $data['output_tokens'] ?? 0,
            'estimated_cost' => $inputCost + $outputCost,
            'request_type' => $data['request_type'] ?? 'chat',
        ]);
    }
}
