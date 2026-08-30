<?php
/**
 * Integration coverage for AuthService::authenticate — the login boundary.
 *
 * All failure modes must return null with the SAME shape (uniform-null
 * anti-enumeration contract); success must issue a verifiable token whose
 * sub/email/role claims match the user, and never leak the password hash.
 *
 * Runs against hr_budget_test; the base TestCase wraps each test in a
 * transaction that is rolled back, so the created user never persists.
 */

namespace Tests\Integration;

use App\Core\Jwt;
use App\Services\AuthService;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private const PASSWORD = 'pass1234';

    private AuthService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AuthService();
    }

    /** @test */
    public function wrong_password_returns_null(): void
    {
        $user = $this->createUser(['password' => self::PASSWORD]);

        $result = $this->service->authenticate((string) $user['email'], 'wrong-password');

        $this->assertNull($result);
    }

    /** @test */
    public function unknown_email_returns_null(): void
    {
        $this->createUser(['password' => self::PASSWORD]);

        $result = $this->service->authenticate('nobody-' . uniqid() . '@moj.go.th', self::PASSWORD);

        $this->assertNull($result);
    }

    /** @test */
    public function inactive_user_returns_null(): void
    {
        $user = $this->createUser([
            'password' => self::PASSWORD,
            'is_active' => 0,
        ]);

        $result = $this->service->authenticate((string) $user['email'], self::PASSWORD);

        $this->assertNull($result);
    }

    /** @test */
    public function success_returns_verifiable_token_and_matching_claims(): void
    {
        $user = $this->createUser([
            'password' => self::PASSWORD,
            'role' => 'editor',
        ]);

        $result = $this->service->authenticate((string) $user['email'], self::PASSWORD);

        $this->assertNotNull($result);
        $payload = Jwt::verify($result->token);
        $this->assertNotNull($payload);
        $this->assertSame((string) $user['id'], $payload['sub']);
        $this->assertSame((string) $user['email'], $payload['email']);
        $this->assertSame('editor', $payload['role']);
        $this->assertGreaterThan(0, $result->expiresIn);
    }

    /** @test */
    public function success_response_never_exposes_the_password_hash(): void
    {
        $user = $this->createUser(['password' => self::PASSWORD]);

        $result = $this->service->authenticate((string) $user['email'], self::PASSWORD);

        $this->assertNotNull($result);
        // The serialized payload is the client-facing contract — the raw DB row
        // inside the DTO may carry the hash, but toArray() must whitelist it out.
        $data = $result->toArray();
        $this->assertArrayNotHasKey('password', $data['user']);
        $this->assertSame((string) $user['email'], $data['user']['email']);
    }
}
