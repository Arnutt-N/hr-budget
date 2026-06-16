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
     * Clears the auth cookie. Always 204 — logging out twice is not an error.
     */
    public function logout(): void
    {
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
