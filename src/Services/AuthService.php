<?php
/**
 * AuthService — verify credentials, issue JWT.
 *
 * Deliberately returns null for ALL failure cases (not found, wrong password,
 * inactive user). Leaking different error messages enables user enumeration.
 */

namespace App\Services;

use App\Core\Jwt;
use App\Models\User;
use App\Dtos\AuthResponseDto;

final class AuthService
{
    /**
     * Authenticate email+password. Returns token + user info on success.
     * Returns null for any failure mode.
     *
     * Every failure is logged server-side (email + IP + reason) so
     * brute-force / credential-stuffing attacks are visible in ops logs —
     * client-facing response stays uniform to prevent user enumeration.
     */
    public function authenticate(string $email, string $password): ?AuthResponseDto
    {
        $user = User::findByEmail($email);
        if ($user === null) {
            self::logFailure($email, 'user_not_found');
            return null;
        }

        $storedHash = (string) ($user['password'] ?? '');
        if ($storedHash === '' || !password_verify($password, $storedHash)) {
            self::logFailure($email, 'wrong_password');
            return null;
        }

        // Respect is_active if column exists
        if (array_key_exists('is_active', $user) && !$user['is_active']) {
            self::logFailure($email, 'user_inactive');
            return null;
        }

        $cfg = require __DIR__ . '/../../config/api.php';
        $token = Jwt::issue((int) $user['id'], [
            'email' => (string) ($user['email'] ?? ''),
            'role'  => (string) ($user['role'] ?? 'user'),
        ]);

        return new AuthResponseDto(
            token: $token,
            expiresIn: (int) $cfg['jwt_ttl'],
            user: $user,
        );
    }

    /**
     * Audit-log a failed login. Goes to PHP's error log; never exposed to the client.
     * Include reason so ops can distinguish brute-force vs inactive-user sweeps.
     */
    private static function logFailure(string $email, string $reason): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '?';
        $userAgent = substr($_SERVER['HTTP_USER_AGENT'] ?? '?', 0, 120);
        error_log("[auth] login_failed reason={$reason} email={$email} ip={$ip} ua=\"{$userAgent}\"");
    }
}
