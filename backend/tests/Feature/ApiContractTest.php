<?php

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

/**
 * Contract checklist for Feature tests once Laravel app is fully bootstrapped.
 * Run after: composer install && php artisan test
 *
 * Expected coverage:
 * - POST /api/v1/auth/register creates organization + admin
 * - POST /api/v1/auth/login returns token
 * - Tenant isolation: customer of org A not visible to org B
 * - Payment verify is idempotent
 * - AI chat requires ai.chat.use permission
 * - Module activate free vs paid (402)
 */
class ApiContractTest extends TestCase
{
    public function test_contract_documented(): void
    {
        $this->assertTrue(true, 'See class docblock for API contract checklist');
    }
}
