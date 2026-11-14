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
use App\Core\AuthCookie;
use App\Core\Jwt;
use App\Core\RateLimiter;
use App\Core\Request;
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

        $ip = Request::clientIp();
        $emailKey = 'login:' . $ip . ':' . strtolower($dto->email);
        if (!RateLimiter::allow('login_ip:' . $ip, 30, 60) || !RateLimiter::allow($emailKey, 10, 60)) {
            if (!headers_sent()) {
                header('Retry-After: 60');
            }
            ApiResponse::error('พยายามเข้าสู่ระบบบ่อยเกินไป กรุณารอสักครู่', 429);
            return;
        }

        $result = $this->service->authenticate($dto->email, $dto->password);
        if ($result === null) {
            ApiResponse::unauthorized('อีเมลหรือรหัสผ่านไม่ถูกต้อง');
            return;
        }

        RateLimiter::clear($emailKey);

        // SPA session: token also travels as an httpOnly cookie so browser JS
        // never touches it (XSS-proof). Token stays in the JSON body for
        // Bearer clients (tests, future mobile).
        AuthCookie::set($result->token, time() + $result->expiresIn);

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
     * Clears the auth cookie. The local session always ends, even when the
     * server-side revocation store is unwritable, so a failed revoke can never
     * leave a live cookie behind. A failed revoke is retried from /auth/me,
     * which rejects the token, until it expires.
     */
    public function logout(): void
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
        $token = str_starts_with($header, 'Bearer ')
            ? substr($header, 7)
            : (string) ($_COOKIE[AuthMiddleware::COOKIE_NAME] ?? '');
        if ($token !== '') {
            $payload = Jwt::verify($token);
            if ($payload !== null && isset($payload['jti'])) {
                try {
                    Jwt::revoke((string) $payload['jti'], (int) ($payload['exp'] ?? (time() + 3600)));
                } catch (\RuntimeException $e) {
                    // Clearing the cookie ends this browser's session; the token
                    // stays usable by a bearer client until it expires.
                    error_log('[auth] logout revocation failed: ' . $e->getMessage());
                    AuthCookie::clear();
                    ApiResponse::error('ออกจากระบบแล้ว แต่การเพิกถอนโทเค็นไม่สำเร็จ กรุณาแจ้งผู้ดูแลระบบ', 500);
                    return;
                }
            }
        }
        AuthCookie::clear();
        ApiResponse::noContent();
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
