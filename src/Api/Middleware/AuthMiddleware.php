<?php
/**
 * JWT Auth Middleware for protected API endpoints.
 *
 * Usage inside a controller method:
 *     $user = AuthMiddleware::require();
 *
 * Rejects with 401 JSON if token missing / invalid / expired, or user
 * is deleted / inactive.
 *
 * Apache sometimes strips the Authorization header — we check both
 * HTTP_AUTHORIZATION and REDIRECT_HTTP_AUTHORIZATION. On topzlab shared
 * hosting an .htaccess rule may also be needed:
 *     RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]
 */

namespace App\Api\Middleware;

use App\Core\Jwt;
use App\Models\User;
use App\Api\Responses\ApiResponse;

final class AuthMiddleware
{
    /** @var array<string,mixed>|null */
    private static ?array $user = null;

    /**
     * Resolve and return the current authenticated user (as array).
     * Exits with 401 JSON on any failure.
     *
     * @return array<string,mixed>
     */
    public static function require(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (!str_starts_with($header, 'Bearer ')) {
            ApiResponse::unauthorized('Missing Bearer token');
        }

        $token = substr($header, 7);
        $payload = Jwt::verify($token);
        if ($payload === null) {
            ApiResponse::unauthorized('Invalid or expired token');
        }

        $userId = (int) ($payload['sub'] ?? 0);
        if ($userId <= 0) {
            ApiResponse::unauthorized('Invalid token subject');
        }

        $user = User::find($userId);
        if ($user === null) {
            ApiResponse::unauthorized('User not found');
        }

        // Respect is_active if the column exists
        if (array_key_exists('is_active', $user) && !$user['is_active']) {
            ApiResponse::unauthorized('User is inactive');
        }

        self::$user = $user;
        return $user;
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        return self::$user;
    }
}
