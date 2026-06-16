<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Request-scoped helpers. Currently: authoritative HTTPS detection that works
 * both for direct TLS and behind a TLS-terminating reverse proxy.
 *
 * X-Forwarded-Proto is client-controllable, so it is honored ONLY when the
 * operator explicitly opts in with TRUST_PROXY=true (i.e. they know PHP sits
 * behind a trusted proxy and is not directly reachable). Default off = a
 * spoofed header cannot flip `secure` cookies or HSTS on.
 */
final class Request
{
    public static function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if (self::trustProxy()) {
            // The header may be a comma-separated chain ("https, http") — the
            // left-most value is the original client-facing scheme.
            $raw = (string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '');
            $first = strtolower(trim(explode(',', $raw)[0]));
            if ($first === 'https') {
                return true;
            }
        }

        return false;
    }

    private static function trustProxy(): bool
    {
        return filter_var($_ENV['TRUST_PROXY'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }
}
