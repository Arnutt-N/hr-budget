<?php
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

        ApiResponse::ok($result->toArray());
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
