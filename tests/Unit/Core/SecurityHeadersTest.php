<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\SecurityHeaders;
use PHPUnit\Framework\TestCase;

final class SecurityHeadersTest extends TestCase
{
    public function testBaselineHttpHasCoreHeadersAndNoHsts(): void
    {
        $joined = implode("\n", SecurityHeaders::baselineHeaders(false));

        $this->assertStringContainsString('X-Content-Type-Options: nosniff', $joined);
        $this->assertStringContainsString('X-Frame-Options: DENY', $joined);
        $this->assertStringContainsString('Referrer-Policy: strict-origin-when-cross-origin', $joined);
        $this->assertStringContainsString('Permissions-Policy:', $joined);
        $this->assertStringNotContainsString('Strict-Transport-Security', $joined);
    }

    public function testBaselineHttpsAddsHsts(): void
    {
        $joined = implode("\n", SecurityHeaders::baselineHeaders(true));

        $this->assertStringContainsString('Strict-Transport-Security: max-age=31536000', $joined);
    }

    public function testSpaCspIsStrict(): void
    {
        $csp = SecurityHeaders::spaCsp();

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("script-src 'self'", $csp);
        // The script-src directive must not permit inline scripts.
        $this->assertDoesNotMatchRegularExpression("/script-src[^;]*unsafe-inline/", $csp);
    }
}
