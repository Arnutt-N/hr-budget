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

    /**
     * Direct connections: REMOTE_ADDR is authoritative.
     * Behind a trusted reverse proxy (TRUST_PROXY=true): the left-most
     * X-Forwarded-For entry is the original client (right-most is added by the
     * nearest trusted proxy; entries in between are client-spoofable).
     * Left-most is correct only when the proxy overwrites XFF rather than
     * appends to a client-supplied chain; verify the proxy's behavior before
     * enabling TRUST_PROXY.
     */
    public static function clientIp(): string
    {
        if (self::trustProxy()) {
            $raw = (string) ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? '');
            $first = trim(explode(',', $raw)[0]);
            $ip = self::normalizeIp($first);
            if ($ip !== null) {
                return $ip;
            }
        }
        return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    }

    /**
     * Accept an X-Forwarded-For entry only when it is a bare IP. Proxies
     * commonly emit "[2001:db8::1]:443" or "198.51.100.7:443"; strip the
     * brackets and port so those still identify the client instead of falling
     * back to the proxy address and collapsing everyone into one bucket.
     * Anything that is not an IP (a hostname, garbage) returns null.
     */
    private static function normalizeIp(string $value): ?string
    {
        if ($value === '') {
            return null;
        }

        $candidate = $value;
        if (str_starts_with($candidate, '[')) {
            $end = strpos($candidate, ']');
            if ($end === false) {
                return null;
            }
            $candidate = substr($candidate, 1, $end - 1);
        } elseif (substr_count($candidate, ':') === 1) {
            $candidate = strstr($candidate, ':', true);
        }

        return filter_var($candidate, FILTER_VALIDATE_IP) === false ? null : $candidate;
    }
}
