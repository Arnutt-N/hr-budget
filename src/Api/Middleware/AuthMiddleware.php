<?php

declare(strict_types=1);

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
    /** Cookie carrying the JWT for the SPA. Set/cleared by AuthController. */
    public const COOKIE_NAME = 'hr_budget_token';

    /** @var array<string,mixed>|null */
    private static ?array $user = null;

    /**
     * Resolve and return the current authenticated user (as array).
     * Exits with 401 JSON on any failure.
     *
     * Accepts the token from (in order):
     *   1. Authorization: Bearer header (tests, mobile, tools)
     *   2. httpOnly cookie (SPA) — mutating requests must also carry
     *      X-Requested-With: XMLHttpRequest as a CSRF guard, since cross-site
     *      forms cannot set custom headers without a CORS preflight.
     *
     * @return array<string,mixed>
     */
    public static function require(): array
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if (str_starts_with($header, 'Bearer ')) {
            $token = substr($header, 7);
        } else {
            $token = (string) ($_COOKIE[self::COOKIE_NAME] ?? '');
            if ($token === '') {
                self::logDenied('missing_token');
                ApiResponse::unauthorized('Missing Bearer token');
            }

            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
            $isMutation = !in_array($method, ['GET', 'HEAD', 'OPTIONS'], true);
            if ($isMutation && ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
                self::logDenied('csrf_header_missing', ['method' => $method]);
                ApiResponse::forbidden('Missing CSRF header');
            }
        }

        $payload = Jwt::verify($token);
        if ($payload === null) {
            // Jwt::verify already logged the specific exception server-side.
            ApiResponse::unauthorized('Invalid or expired token');
        }

        $userId = (int) ($payload['sub'] ?? 0);
        if ($userId <= 0) {
            self::logDenied('invalid_subject', ['sub' => $payload['sub'] ?? null]);
            ApiResponse::unauthorized('Invalid token subject');
        }

        $user = User::find($userId);
        if ($user === null) {
            // Valid signed token but user is gone (deleted / DB desync).
            // This is worth surfacing because it suggests tokens outliving
            // their user records — indicates operational issue.
            self::logDenied('user_not_found', ['user_id' => $userId]);
            ApiResponse::unauthorized('User not found');
        }

        // Respect is_active if the column exists
        if (array_key_exists('is_active', $user) && !$user['is_active']) {
            self::logDenied('user_inactive', ['user_id' => $userId]);
            ApiResponse::unauthorized('User is inactive');
        }

        unset($user['password']);

        self::$user = $user;
        return $user;
    }

    /**
     * Audit-log a denied request. Goes to PHP error log only; never exposed.
     * @param array<string,mixed> $context
     */
    private static function logDenied(string $reason, array $context = []): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '?';
        // Strip CR/LF so a crafted URI cannot inject fake log lines
        $path = preg_replace('/[\r\n]/', '', $_SERVER['REQUEST_URI'] ?? '?');
        $extra = $context === [] ? '' : ' ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        error_log("[auth] api_denied reason={$reason} path={$path} ip={$ip}{$extra}");
    }

    /** @return array<string,mixed>|null */
    public static function user(): ?array
    {
        return self::$user;
    }
}
