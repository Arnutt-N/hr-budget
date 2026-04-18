<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Core\Jwt;

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
}
