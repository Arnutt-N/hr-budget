<?php
/**
 * Cookie-based API auth (Phase 1 — SPA auth hardening).
 *
 * Covers the in-process testable surface: token resolution from the
 * httpOnly cookie, CSRF-header acceptance, and Bearer precedence.
 *
 * Rejection paths (missing token → 401, missing X-Requested-With on a
 * cookie-authed mutation → 403) call ApiResponse with exit=true and so
 * cannot be asserted in-process without killing the runner — they are
 * covered over real HTTP in tests/e2e/auth-login-logout.spec.ts.
 */

namespace Tests\Integration;

use Tests\TestCase;
use App\Api\Middleware\AuthMiddleware;
use App\Core\Jwt;

class AuthCookieTest extends TestCase
{
    protected function tearDown(): void
    {
        // Never leak auth superglobals into other tests
        unset(
            $_COOKIE[AuthMiddleware::COOKIE_NAME],
            $_SERVER['HTTP_AUTHORIZATION'],
            $_SERVER['HTTP_X_REQUESTED_WITH'],
            $_SERVER['REQUEST_METHOD'],
        );

        parent::tearDown();
    }

    /** @test */
    public function middleware_accepts_token_from_cookie_for_get(): void
    {
        $user = $this->createUser();

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_COOKIE[AuthMiddleware::COOKIE_NAME] = Jwt::issue((int) $user['id']);

        $resolved = AuthMiddleware::require();

        $this->assertSame((int) $user['id'], (int) $resolved['id']);
        $this->assertArrayNotHasKey('password', $resolved);
    }

    /** @test */
    public function middleware_accepts_cookie_mutation_with_csrf_header(): void
    {
        $user = $this->createUser();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_COOKIE[AuthMiddleware::COOKIE_NAME] = Jwt::issue((int) $user['id']);

        $resolved = AuthMiddleware::require();

        $this->assertSame((int) $user['id'], (int) $resolved['id']);
    }

    /** @test */
    public function middleware_prefers_bearer_header_over_cookie(): void
    {
        $bearerUser = $this->createUser(['name' => 'Bearer User']);
        $cookieUser = $this->createUser(['name' => 'Cookie User']);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . Jwt::issue((int) $bearerUser['id']);
        $_COOKIE[AuthMiddleware::COOKIE_NAME] = Jwt::issue((int) $cookieUser['id']);

        $resolved = AuthMiddleware::require();

        $this->assertSame((int) $bearerUser['id'], (int) $resolved['id']);
    }
}
