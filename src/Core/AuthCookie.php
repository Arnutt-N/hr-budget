<?php

declare(strict_types=1);

namespace App\Core;

use App\Api\Middleware\AuthMiddleware;

/**
 * Sets / clears the SPA's JWT auth cookie (hr_budget_token).
 *
 * Extracted from AuthController so the password-login flow and the ThaID
 * OAuth callback mint the cookie with byte-for-byte identical security
 * attributes (httpOnly, SameSite=Strict, secure). Keep this the SINGLE place
 * those flags live — drift here is a security bug.
 */
final class AuthCookie
{
    public static function set(string $token, int $expires): void
    {
        self::write($token, $expires);
    }

    public static function clear(): void
    {
        self::write('', time() - 3600);
    }

    private static function write(string $value, int $expires): void
    {
        if (headers_sent()) {
            // Expected under PHPUnit (bootstrap flushes output); anywhere else
            // it means the cookie was silently dropped — surface it.
            if (($_ENV['APP_ENV'] ?? '') !== 'testing') {
                error_log('[auth] set_cookie_failed reason=headers_sent');
            }
            return;
        }

        // `secure` follows COOKIE_SECURE (.env) when set; otherwise it tracks the
        // actual request scheme (incl. X-Forwarded-Proto behind a trusted proxy),
        // so it is correct on HTTPS and never set on Laragon's plain-http dev.
        $secure = isset($_ENV['COOKIE_SECURE'])
            ? $_ENV['COOKIE_SECURE'] === 'true'
            : Request::isHttps();

        setcookie(AuthMiddleware::COOKIE_NAME, $value, [
            'expires'  => $expires,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure'   => $secure,
        ]);
    }
}
