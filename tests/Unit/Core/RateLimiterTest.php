<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\RateLimiter;
use PHPUnit\Framework\TestCase;

final class RateLimiterTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hr_budget_rl_test_' . bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        if (is_dir($this->dir)) {
            foreach (glob($this->dir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
                @unlink($file);
            }
            @rmdir($this->dir);
        }
        parent::tearDown();
    }

    public function testAllowsUpToMaxAttemptsThenBlocks(): void
    {
        $key = 'login:127.0.0.1:user@example.com';

        $this->assertTrue(RateLimiter::allow($key, 3, 60, $this->dir));
        $this->assertTrue(RateLimiter::allow($key, 3, 60, $this->dir));
        $this->assertTrue(RateLimiter::allow($key, 3, 60, $this->dir));
        $this->assertFalse(RateLimiter::allow($key, 3, 60, $this->dir));
    }

    public function testClearResetsCounter(): void
    {
        $key = 'login:127.0.0.1:reset@example.com';

        $this->assertTrue(RateLimiter::allow($key, 1, 60, $this->dir));
        $this->assertFalse(RateLimiter::allow($key, 1, 60, $this->dir));

        RateLimiter::clear($key, $this->dir);
        $this->assertTrue(RateLimiter::allow($key, 1, 60, $this->dir));
    }
}
