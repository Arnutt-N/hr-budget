<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Core\Jwt;
use Firebase\JWT\JWT as FirebaseJwt;

class JwtTest extends TestCase
{
    /** @test */
    public function issue_and_verify_roundtrip(): void
    {
        $token = Jwt::issue(42, ['email' => 'user@example.com', 'role' => 'admin']);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertCount(3, explode('.', $token));

        $payload = Jwt::verify($token);

        $this->assertNotNull($payload);
        $this->assertSame('42', $payload['sub']);
        $this->assertSame('user@example.com', $payload['email']);
        $this->assertSame('admin', $payload['role']);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
    }

    /** @test */
    public function verify_returns_null_for_garbage_input(): void
    {
        $this->assertNull(Jwt::verify('not-a-jwt'));
        $this->assertNull(Jwt::verify(''));
        $this->assertNull(Jwt::verify('x.y.z'));
    }

    /** @test */
    public function verify_returns_null_for_tampered_token(): void
    {
        $token = Jwt::issue(1);
        $tampered = $token . 'x';
        $this->assertNull(Jwt::verify($tampered));
    }

    /** @test */
    public function verify_returns_null_for_wrong_signature(): void
    {
        $token = Jwt::issue(1);
        $parts = explode('.', $token);
        // Replace signature with garbage of same length
        $parts[2] = str_repeat('a', strlen($parts[2]));
        $forged = implode('.', $parts);
        $this->assertNull(Jwt::verify($forged));
    }

    /** @test */
    public function verify_returns_null_for_expired_token(): void
    {
        $token = FirebaseJwt::encode(
            [
                'iss' => 'jwt-test',
                'iat' => time() - 100,
                'exp' => time() - 10,
                'sub' => '1',
            ],
            $this->currentSecret(),
            'HS256',
        );

        $this->assertNull(Jwt::verify($token));
    }

    /** @test */
    public function issue_rejects_placeholder_secret(): void
    {
        $this->assertSecretRejected('changeme');
    }

    /** @test */
    public function issue_rejects_short_secret(): void
    {
        $this->assertSecretRejected('abc');
    }

    /**
     * Force the next Jwt::config() rebuild to pick up $_ENV['JWT_SECRET'],
     * expect the safety assertion to throw, then restore everything.
     */
    private function assertSecretRejected(string $badSecret): void
    {
        $original = $_ENV['JWT_SECRET'] ?? null;
        try {
            $_ENV['JWT_SECRET'] = $badSecret;
            $this->resetJwtConfig();
            $this->expectException(\RuntimeException::class);
            Jwt::issue(1);
        } finally {
            if ($original === null) {
                unset($_ENV['JWT_SECRET']);
            } else {
                $_ENV['JWT_SECRET'] = $original;
            }
            $this->resetJwtConfig();
        }
    }

    private function currentSecret(): string
    {
        return (string) ($_ENV['JWT_SECRET'] ?? '');
    }

    /** Jwt::$config is a private static cache — clear it so config/api.php re-reads env. */
    private function resetJwtConfig(): void
    {
        $prop = new \ReflectionProperty(Jwt::class, 'config');
        $prop->setValue(null, null);
    }
}
