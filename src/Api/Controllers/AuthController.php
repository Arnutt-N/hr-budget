<?php

declare(strict_types=1);

/**
 * Auth REST Controller — thin transport layer.
 *
 * Parses request → calls AuthService → renders ApiResponse.
 * No business logic here.
 */

namespace App\Api\Controllers;

use App\Api\Responses\ApiResponse;
use App\Api\Middleware\AuthMiddleware;
use App\Dtos\LoginRequestDto;
use App\Services\AuthService;

final class AuthController
{
    public function __construct(
        private readonly AuthService $service = new AuthService(),
    ) {}

    /**
     * POST /api/v1/auth/login
     * Body: { email, password }
     * 200 -> { token, expires_in, user }
     * 401 -> Invalid credentials
     * 422 -> Validation failed (field details)
     */
    public function login(): void
    {
        $dto = LoginRequestDto::fromRequest();

        $errors = $dto->validate();
        if ($errors !== []) {
            ApiResponse::validationFailed($errors);
            return;
        }

        $result = $this->service->authenticate($dto->email, $dto->password);
        if ($result === null) {
            ApiResponse::unauthorized('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
            return;
        }

        // SPA session: token also travels as an httpOnly cookie so browser JS
        // never touches it (XSS-proof). Token stays in the JSON body for
        // Bearer clients (tests, future mobile).
        self::setTokenCookie($result->token, time() + $result->expiresIn);

        // SPA requests (marked by the CSRF header) get the cookie only —
        // omitting the token from the body keeps it out of JS reach entirely.
        $data = $result->toArray();
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
            unset($data['token']);
        }

        ApiResponse::ok($data);
    }

    /**
     * POST /api/v1/auth/logout
     * Clears the auth cookie. Always 204 — logging out twice is not an error.
     */
    public function logout(): void
    {
        self::setTokenCookie('', time() - 3600);
        ApiResponse::noContent();
    }

    /**
     * Cookie name doubles as the contract with AuthMiddleware::COOKIE_NAME.
     * `secure` is controlled by COOKIE_SECURE (.env), falling back to "on in
     * production" — Laragon dev runs plain http and a Secure cookie would
     * silently never be stored.
     */
    private static function setTokenCookie(string $value, int $expires): void
    {
        if (headers_sent()) {
            // Expected under PHPUnit (bootstrap flushes output); anywhere else
            // it means the session cookie was silently dropped — surface it.
            if (($_ENV['APP_ENV'] ?? '') !== 'testing') {
                error_log('[auth] set_cookie_failed reason=headers_sent');
            }
            return;
        }

        $secure = isset($_ENV['COOKIE_SECURE'])
            ? $_ENV['COOKIE_SECURE'] === 'true'
            : ($_ENV['APP_ENV'] ?? '') === 'production';

        setcookie(AuthMiddleware::COOKIE_NAME, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => $secure,
        ]);
    }

    /**
     * GET /api/v1/auth/me
     * Returns current user info — requires Bearer token.
     */
    public function me(): void
    {
        $user = AuthMiddleware::require();

        ApiResponse::ok([
            'id'    => (int) ($user['id'] ?? 0),
            'email' => (string) ($user['email'] ?? ''),
            'name'  => (string) ($user['name'] ?? ''),
            'role'  => (string) ($user['role'] ?? 'user'),
        ]);
    }
}
