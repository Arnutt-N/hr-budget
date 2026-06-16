<?php

declare(strict_types=1);

namespace App\Dtos;

/**
 * Normalized ThaID identity resolved from the DOPA userinfo response.
 *
 * The exact DOPA claim names are the one external unknown in this feature, so
 * fromUserInfo() reads them through a caller-supplied field map (sourced from
 * config) — correcting a wrong guess is a config edit, not a code change.
 *
 * `sub` is the canonical, stable identity key (PID / OIDC subject). `email`
 * may be absent or unverified; `emailVerified` gates any account-linking by
 * email to prevent takeover via an attacker-set unverified address.
 */
final class ThaIdIdentityDto
{
    public function __construct(
        public readonly string $sub,
        public readonly string $nameTh,
        public readonly string $email,
        public readonly bool $emailVerified,
    ) {}

    /**
     * @param array<string,mixed>  $json      decoded userinfo payload
     * @param array<string,string> $fieldMap  keys: sub, name, email, email_verified
     */
    public static function fromUserInfo(array $json, array $fieldMap): self
    {
        $pick = static function (string $key) use ($json, $fieldMap): string {
            $claim = $fieldMap[$key] ?? $key;
            return trim((string) ($json[$claim] ?? ''));
        };

        $verifiedClaim = $fieldMap['email_verified'] ?? 'email_verified';
        $verified = filter_var($json[$verifiedClaim] ?? false, FILTER_VALIDATE_BOOLEAN);

        return new self(
            sub: $pick('sub'),
            nameTh: $pick('name'),
            email: $pick('email'),
            emailVerified: $verified,
        );
    }
}
