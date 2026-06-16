<?php

declare(strict_types=1);

namespace App\Services;

/**
 * ThaID feature gate + typed accessor over the config/auth.php `thaid` block.
 *
 * The integration is DORMANT BY DEFAULT:
 *   - isRealEnabled()  → true only when client_id + client_secret + redirect_uri
 *                        are all set (admin supplied real DOPA credentials)
 *   - isEnabled()      → real flow OR the dev mock (THAID_MOCK=true) in a
 *                        non-production env; never the mock in production.
 *
 * Inject the array in tests; in production it self-loads from config/auth.php.
 */
final class ThaIdConfig
{
    /** @var array<string,mixed> */
    private array $cfg;

    /** @param array<string,mixed>|null $thaid inject for tests; null = load config */
    public function __construct(?array $thaid = null)
    {
        if ($thaid === null) {
            $auth = require __DIR__ . '/../../config/auth.php';
            $thaid = $auth['thaid'] ?? [];
        }
        $this->cfg = $thaid;

        // PKCE-off is a hedge for a non-conformant provider, never a default.
        // Make the weakened posture auditable when the real flow is live.
        if (!$this->pkce() && $this->isRealEnabled()) {
            error_log('[thaid] WARNING: PKCE disabled — code-interception protection off; do not run this way in production');
        }
    }

    public function isMock(): bool
    {
        return filter_var($this->cfg['mock'] ?? false, FILTER_VALIDATE_BOOLEAN);
    }

    public function hasCredentials(): bool
    {
        return $this->clientId() !== ''
            && $this->clientSecret() !== ''
            && $this->redirectUri() !== '';
    }

    public function isRealEnabled(): bool
    {
        return $this->hasCredentials();
    }

    public function isProd(): bool
    {
        // Fail-safe: an unknown environment is treated as production (mock off).
        // Read $_ENV first, then getenv(), to cover every dotenv loading mode.
        $env = $_ENV['APP_ENV'] ?? (getenv('APP_ENV') ?: 'production');
        return $env === 'production';
    }

    /** The feature is reachable at all only when this is true. */
    public function isEnabled(): bool
    {
        return $this->isRealEnabled() || ($this->isMock() && !$this->isProd());
    }

    public function clientId(): string     { return (string) ($this->cfg['client_id'] ?? ''); }
    public function clientSecret(): string { return (string) ($this->cfg['client_secret'] ?? ''); }
    public function redirectUri(): string  { return (string) ($this->cfg['redirect_uri'] ?? ''); }
    public function authorizeUrl(): string { return (string) ($this->cfg['authorize_url'] ?? ''); }
    public function tokenUrl(): string     { return (string) ($this->cfg['token_url'] ?? ''); }
    public function userinfoUrl(): string  { return (string) ($this->cfg['userinfo_url'] ?? ''); }
    public function scope(): string        { return (string) ($this->cfg['scope'] ?? 'pid name'); }
    public function pkce(): bool           { return filter_var($this->cfg['pkce'] ?? true, FILTER_VALIDATE_BOOLEAN); }
    public function clientAuth(): string   { return (string) ($this->cfg['client_auth'] ?? 'basic'); }

    /** @return array<string,string> */
    public function fieldMap(): array
    {
        $map = $this->cfg['field_map'] ?? [];
        return is_array($map) ? array_map('strval', $map) : [];
    }
}
