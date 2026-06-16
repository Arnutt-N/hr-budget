<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\ThaIdConfig;
use PHPUnit\Framework\TestCase;

/**
 * The feature gate: dormant by default, real-enabled only with credentials,
 * mock only in non-production.
 */
final class ThaIdConfigTest extends TestCase
{
    private const REAL = [
        'mock'          => false,
        'client_id'     => 'cid',
        'client_secret' => 'sec',
        'redirect_uri'  => 'https://app.example/callback',
    ];

    public function test_real_credentials_enable_the_feature(): void
    {
        $cfg = new ThaIdConfig(self::REAL);
        $this->assertTrue($cfg->hasCredentials());
        $this->assertTrue($cfg->isRealEnabled());
        $this->assertTrue($cfg->isEnabled());
        $this->assertFalse($cfg->isMock());
    }

    public function test_empty_config_is_dormant(): void
    {
        $cfg = new ThaIdConfig([]);
        $this->assertFalse($cfg->hasCredentials());
        $this->assertFalse($cfg->isRealEnabled());
        $this->assertFalse($cfg->isEnabled());
    }

    public function test_partial_credentials_stay_dormant(): void
    {
        $cfg = new ThaIdConfig(['client_id' => 'cid', 'client_secret' => 'sec']); // no redirect_uri
        $this->assertFalse($cfg->isEnabled());
    }

    public function test_mock_enables_in_non_production(): void
    {
        $prev = $_ENV['APP_ENV'] ?? null;
        $_ENV['APP_ENV'] = 'testing';
        try {
            $cfg = new ThaIdConfig(['mock' => true]);
            $this->assertTrue($cfg->isMock());
            $this->assertFalse($cfg->isRealEnabled());
            $this->assertTrue($cfg->isEnabled());
        } finally {
            $this->restoreEnv($prev);
        }
    }

    public function test_mock_is_blocked_in_production(): void
    {
        $prev = $_ENV['APP_ENV'] ?? null;
        $_ENV['APP_ENV'] = 'production';
        try {
            $cfg = new ThaIdConfig(['mock' => true]);
            $this->assertTrue($cfg->isMock());
            $this->assertFalse($cfg->isEnabled());
        } finally {
            $this->restoreEnv($prev);
        }
    }

    private function restoreEnv(?string $prev): void
    {
        if ($prev === null) {
            unset($_ENV['APP_ENV']);
        } else {
            $_ENV['APP_ENV'] = $prev;
        }
    }
}
