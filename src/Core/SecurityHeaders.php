<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Baseline security response headers + Content-Security-Policy.
 *
 * Two-tier strategy (Phase 6 cutover left a small legacy server-rendered
 * remnant beside the Vue SPA):
 *   - applyBaseline(): headers that break nothing — applied to every non-API
 *     response (SPA shell, legacy budget-execution + file-vault pages).
 *   - applySpaCsp(): baseline + a strict CSP, applied ONLY to the compiled SPA
 *     shell, which loads only an external module script (no inline scripts).
 *
 * Legacy views still contain inline <script>/<style>, so a script-restricting
 * CSP is intentionally NOT applied to them; they keep the baseline headers
 * (notably nosniff, the key stored-XSS mitigation for the document vault).
 *
 * Pure methods return data (unit-testable); only apply*() emit headers.
 */
final class SecurityHeaders
{
    /**
     * Universally safe security headers. HSTS only over HTTPS.
     *
     * @return list<string>
     */
    public static function baselineHeaders(bool $isHttps): array
    {
        $headers = [
            'X-Content-Type-Options: nosniff',
            'X-Frame-Options: DENY',
            'Referrer-Policy: strict-origin-when-cross-origin',
            'Permissions-Policy: camera=(), microphone=(), geolocation=()',
        ];

        if ($isHttps) {
            $headers[] = 'Strict-Transport-Security: max-age=31536000; includeSubDomains';
        }

        return $headers;
    }

    /**
     * Strict CSP for the SPA shell. style-src keeps 'unsafe-inline' because
     * Vue/PrimeVue inject runtime inline styles; Google Fonts origins are
     * whitelisted (the shell links fonts.googleapis.com / fonts.gstatic.com).
     */
    public static function spaCsp(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "form-action 'self'",
            "img-src 'self' data:",
            "font-src 'self' https://fonts.gstatic.com data:",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "script-src 'self'",
            "connect-src 'self'",
        ]);
    }

    /**
     * Apply baseline headers for the current request.
     */
    public static function applyBaseline(): void
    {
        foreach (self::baselineHeaders(self::isHttps()) as $header) {
            header($header);
        }
    }

    /**
     * Apply the strict SPA Content-Security-Policy only.
     *
     * Baseline headers are applied separately by the bootstrap
     * (public/index.php) for every non-API response, so this adds just the CSP
     * to avoid emitting the baseline set twice on the SPA shell.
     */
    public static function applyCsp(): void
    {
        header('Content-Security-Policy: ' . self::spaCsp());
    }

    private static function isHttps(): bool
    {
        return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    }
}
