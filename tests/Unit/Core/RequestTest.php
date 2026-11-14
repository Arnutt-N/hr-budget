<?php

declare(strict_types=1);

namespace Tests\Unit\Core;

use App\Core\Request;
use PHPUnit\Framework\TestCase;

/**
 * HTTPS detection incl. X-Forwarded-Proto, which is honored ONLY when
 * TRUST_PROXY=true (otherwise a client could spoof it to flip secure/HSTS).
 */
final class RequestTest extends TestCase
{
    /** @var array<string,mixed> */
    private array $serverBackup;
    private ?string $trustBackup;

    protected function setUp(): void
    {
        $this->serverBackup = $_SERVER;
        $this->trustBackup = isset($_ENV['TRUST_PROXY']) ? (string) $_ENV['TRUST_PROXY'] : null;
        unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO'], $_ENV['TRUST_PROXY']);
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;
        if ($this->trustBackup === null) {
            unset($_ENV['TRUST_PROXY']);
        } else {
            $_ENV['TRUST_PROXY'] = $this->trustBackup;
        }
    }

    public function test_plain_http_is_not_https(): void
    {
        $this->assertFalse(Request::isHttps());
    }

    public function test_direct_tls_is_https(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $this->assertTrue(Request::isHttps());
    }

    public function test_https_off_is_not_https(): void
    {
        $_SERVER['HTTPS'] = 'off';
        $this->assertFalse(Request::isHttps());
    }

    public function test_forwarded_proto_ignored_without_trust_proxy(): void
    {
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertFalse(Request::isHttps()); // not trusted → spoof-proof
    }

    public function test_forwarded_proto_honored_with_trust_proxy(): void
    {
        $_ENV['TRUST_PROXY'] = 'true';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        $this->assertTrue(Request::isHttps());
    }

    public function test_forwarded_proto_http_with_trust_proxy_is_not_https(): void
    {
        $_ENV['TRUST_PROXY'] = 'true';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
        $this->assertFalse(Request::isHttps());
    }

    public function test_forwarded_proto_comma_list_uses_first_value(): void
    {
        $_ENV['TRUST_PROXY'] = 'true';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https, http';
        $this->assertTrue(Request::isHttps());
    }

    public function test_direct_tls_wins_even_if_forwarded_proto_is_http(): void
    {
        $_SERVER['HTTPS'] = 'on';
        $_ENV['TRUST_PROXY'] = 'true';
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
        $this->assertTrue(Request::isHttps());
    }

    public function test_client_ip_uses_remote_addr_by_default(): void
    {
        $_SERVER['REMOTE_ADDR'] = '203.0.113.9';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '1.2.3.4';
        $this->assertSame('203.0.113.9', Request::clientIp());
    }

    public function test_client_ip_uses_forwarded_for_when_trusted(): void
    {
        $_ENV['TRUST_PROXY'] = 'true';
        $_SERVER['REMOTE_ADDR'] = '10.0.0.1';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '198.51.100.7, 10.0.0.1';
        $this->assertSame('198.51.100.7', Request::clientIp());
    }

    public function test_client_ip_falls_back_to_remote_when_forwarded_for_invalid(): void
    {
        $_ENV['TRUST_PROXY'] = 'true';
        $_SERVER['REMOTE_ADDR'] = '203.0.113.9';
        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'not-an-ip, 10.0.0.1';
        $this->assertSame('203.0.113.9', Request::clientIp());
    }

    public function test_client_ip_falls_back_to_unknown_when_no_remote_addr(): void
    {
        $this->assertSame('unknown', Request::clientIp());
    }
}
