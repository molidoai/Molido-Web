<?php

namespace App\Services\AI;

/**
 * MOLIDO AI Safety Gateway
 * Runs before every AI action.
 * Protects against prompt injection, tool abuse, permission escalation, data leakage.
 */
class AISafetyGateway
{
    /**
     * Validate messages and options before allowing AI execution.
     */
    public function validate(array $messages, array $options = []): array
    {
        // 1. Empty check
        if (empty($messages)) {
            return ['allowed' => false, 'reason' => 'Empty messages'];
        }

        // 2. Basic prompt injection patterns
        $lastUserMessage = $this->getLastUserContent($messages);
        if ($lastUserMessage && $this->detectInjection($lastUserMessage)) {
            return ['allowed' => false, 'reason' => 'Potential prompt injection detected'];
        }

        // 3. Permission context required for action mode
        $mode = $options['mode'] ?? 'answer';
        if ($mode === 'action') {
            if (empty($options['user_id']) || empty($options['organization_id'])) {
                return ['allowed' => false, 'reason' => 'Action mode requires authenticated context'];
            }
        }

        // 4. Token / length limits (simple)
        $totalLength = 0;
        foreach ($messages as $msg) {
            $totalLength += strlen($msg['content'] ?? '');
        }
        if ($totalLength > 50000) {
            return ['allowed' => false, 'reason' => 'Input too long'];
        }

        // 5. Blocked keywords for sensitive actions without approval flag
        if ($mode === 'action' && empty($options['human_approved'])) {
            $sensitive = ['refund', 'delete', 'payment', 'transfer', 'password', 'permission'];
            $lower = mb_strtolower($lastUserMessage ?? '');
            foreach ($sensitive as $word) {
                if (str_contains($lower, $word)) {
                    return [
                        'allowed' => false,
                        'reason' => 'Sensitive action requires human approval',
                        'requires_approval' => true,
                    ];
                }
            }
        }

        return ['allowed' => true];
    }

    protected function getLastUserContent(array $messages): ?string
    {
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            if (($messages[$i]['role'] ?? '') === 'user') {
                return $messages[$i]['content'] ?? null;
            }
        }
        return null;
    }

    protected function detectInjection(string $content): bool
    {
        $patterns = [
            '/ignore\s+(all\s+)?(previous|above|prior)\s+instructions/i',
            '/disregard\s+(all\s+)?(previous|prior)/i',
            '/you\s+are\s+now\s+/i',
            '/system\s*:\s*you/i',
            '/\[\[.*system.*\]\]/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }
}
