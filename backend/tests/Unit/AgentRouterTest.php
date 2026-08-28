<?php

namespace Tests\Unit;

use App\Services\AI\AgentRouter;
use PHPUnit\Framework\TestCase;

/**
 * AgentRouter resolve() needs DB for agents.
 * These tests cover buildSystemPrompt with a mock-like stdClass via anonymous class.
 */
class AgentRouterTest extends TestCase
{
    public function test_build_system_prompt_includes_rules(): void
    {
        $router = new AgentRouter();

        $agent = new class {
            public $name = 'Sales AI';
            public $role = 'sales';
            public $system_instructions = 'Help with sales.';
            public $skills = ['lead_management', 'follow_up'];
        };

        $prompt = $router->buildSystemPrompt($agent);

        $this->assertStringContainsString('Help with sales', $prompt);
        $this->assertStringContainsString('lead_management', $prompt);
        $this->assertStringContainsString('Never invent business data', $prompt);
        $this->assertStringContainsString('human approval', strtolower($prompt));
    }
}
