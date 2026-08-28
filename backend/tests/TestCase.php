<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // Requires full Laravel application bootstrap when running Feature tests.
    // Unit tests under tests/Unit can extend PHPUnit\Framework\TestCase directly.
}
