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
     */
    public function authenticate(string $email, string $password): ?AuthResponseDto
    {
        $user = User::findByEmail($email);
        if ($user === null) {
            return null;
        }

        $storedHash = (string) ($user['password'] ?? '');
        if ($storedHash === '' || !password_verify($password, $storedHash)) {
            return null;
        }

        // Respect is_active if column exists
        if (array_key_exists('is_active', $user) && !$user['is_active']) {
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
}
