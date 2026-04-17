<?php
/**
 * Login response payload — never includes password hash.
 *
 * Whitelist user fields explicitly in toArray() so schema changes don't
 * accidentally leak new sensitive columns.
 */

namespace App\Dtos;

final class AuthResponseDto
{
    /** @param array<string,mixed> $user  raw user row from DB */
    public function __construct(
        public readonly string $token,
        public readonly int $expiresIn,
        public readonly array $user,
    ) {}

    /** @return array<string,mixed> */
    public function toArray(): array
    {
        return [
            'token'      => $this->token,
            'expires_in' => $this->expiresIn,
            'user'       => [
                'id'    => (int) ($this->user['id'] ?? 0),
                'email' => (string) ($this->user['email'] ?? ''),
                'name'  => (string) ($this->user['name'] ?? ''),
                'role'  => (string) ($this->user['role'] ?? 'user'),
            ],
        ];
    }
}
