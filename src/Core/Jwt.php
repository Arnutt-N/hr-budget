<?php
/**
 * JWT Helper
 *
 * Thin wrapper around firebase/php-jwt v6+ so application code
 * doesn't need to know about Key objects or exception hierarchy.
 */

namespace App\Core;

use Firebase\JWT\JWT as FirebaseJwt;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

final class Jwt
{
    private static ?array $config = null;

    /**
     * Issue a signed JWT containing subject + optional claims.
     *
     * @param int                  $userId  sub claim (stored as string per RFC 7519)
     * @param array<string,mixed>  $claims  extra claims to merge into payload
     */
    public static function issue(int $userId, array $claims = []): string
    {
        $cfg = self::config();
        $now = time();
        $payload = array_merge([
            'iss' => $_ENV['APP_URL'] ?? 'hr_budget',
            'iat' => $now,
            'exp' => $now + $cfg['jwt_ttl'],
            'sub' => (string) $userId,
        ], $claims);

        return FirebaseJwt::encode($payload, $cfg['jwt_secret'], $cfg['jwt_algo']);
    }

    /**
     * Verify a JWT string and return its payload as an array.
     * Returns null if the token is missing, malformed, expired, or signature is wrong.
     *
     * @return array<string,mixed>|null
     */
    public static function verify(string $token): ?array
    {
        if ($token === '') {
            return null;
        }

        $cfg = self::config();

        try {
            $decoded = FirebaseJwt::decode($token, new Key($cfg['jwt_secret'], $cfg['jwt_algo']));
            return (array) $decoded;
        } catch (ExpiredException $e) {
            // Expired tokens are expected at end of TTL — log at notice, not error.
            error_log('[jwt] token expired: ' . $e->getMessage());
            return null;
        } catch (\Throwable $e) {
            // Malformed / tampered / wrong algorithm / signature mismatch.
            // Client response stays uniform (401), but surface *why* server-side
            // so debugging does not require display_errors in production.
            error_log('[jwt] verify failed: ' . $e::class . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Minimum JWT secret length (bytes). HS256 requires ≥ 32 bytes of entropy
     * to resist signature forgery. Anything shorter is unsafe.
     */
    private const MIN_SECRET_LENGTH = 32;

    /** @var list<string> Known insecure/placeholder values that must never ship. */
    private const FORBIDDEN_SECRETS = [
        '',
        'replace-with-random-64-hex-chars',
        'your-secret-here',
        'changeme',
    ];

    /** @return array<string,mixed> */
    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/api.php';
            self::assertSecretSafe((string) (self::$config['jwt_secret'] ?? ''));
        }
        return self::$config;
    }

    /**
     * Fails loudly at first JWT use if the signing secret is missing, a
     * known-placeholder value, or shorter than 32 bytes. Avoids the silent
     * forgery vector where empty secret → tokens signed with no key.
     */
    private static function assertSecretSafe(string $secret): void
    {
        if (in_array($secret, self::FORBIDDEN_SECRETS, true)) {
            throw new \RuntimeException(
                'JWT_SECRET is not set or uses a placeholder value. ' .
                "Generate one with: php -r \"echo bin2hex(random_bytes(32));\" " .
                'and put it in .env.'
            );
        }
        if (strlen($secret) < self::MIN_SECRET_LENGTH) {
            throw new \RuntimeException(
                'JWT_SECRET is too short (' . strlen($secret) . ' bytes). ' .
                'Minimum is ' . self::MIN_SECRET_LENGTH . ' bytes for HS256 safety.'
            );
        }
    }
}
