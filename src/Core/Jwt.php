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
        } catch (ExpiredException) {
            return null;
        } catch (\Throwable) {
            return null;
        }
    }

    /** @return array<string,mixed> */
    private static function config(): array
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../../config/api.php';
        }
        return self::$config;
    }
}
