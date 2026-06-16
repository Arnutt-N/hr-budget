<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Http\CurlHttpClient;
use App\Dtos\ThaIdIdentityDto;
use App\Models\User;

/**
 * Orchestrates the ThaID OAuth2 login: begin (state + PKCE) and complete
 * (state check → code exchange → userinfo → resolve local user).
 *
 * Deliberately I/O-free with respect to the web request: the controller owns
 * session storage of the state/verifier between begin and complete, so this
 * class is unit-testable with a fake HttpClient and an in-memory DB.
 */
final class ThaIdAuthService
{
    public function __construct(
        private readonly ThaIdConfig $cfg = new ThaIdConfig(),
        private readonly ?ThaIdProvider $provider = null,
    ) {}

    private function provider(): ThaIdProvider
    {
        return $this->provider ?? new ThaIdProvider(new CurlHttpClient(), $this->cfg);
    }

    /**
     * Generate the authorize URL plus the CSRF state and PKCE verifier the
     * controller must persist (session) for the callback.
     *
     * @return array{url:string,state:string,code_verifier:?string}
     */
    public function beginLogin(): array
    {
        $state = bin2hex(random_bytes(16));

        $verifier = null;
        $challenge = null;
        if ($this->cfg->pkce()) {
            $verifier  = self::base64Url(random_bytes(32));
            $challenge = self::base64Url(hash('sha256', $verifier, true));
        }

        return [
            'url'           => $this->provider()->authorizeUrl($state, $challenge),
            'state'         => $state,
            'code_verifier' => $verifier,
        ];
    }

    /**
     * Validate the returned state, exchange the code, resolve the local user.
     *
     * @return array<string,mixed> the resolved (active) user row
     * @throws \RuntimeException on state mismatch, provider failure, or inactive user
     */
    public function completeLogin(
        string $code,
        string $returnedState,
        string $expectedState,
        ?string $codeVerifier,
    ): array {
        if ($expectedState === '' || $returnedState === '' || !hash_equals($expectedState, $returnedState)) {
            throw new \RuntimeException('state_mismatch');
        }

        $accessToken = $this->provider()->exchangeCode($code, $codeVerifier);
        $identity    = $this->provider()->fetchUserInfo($accessToken);

        return $this->resolveUser($identity);
    }

    /**
     * Find-or-create the local user for a verified ThaID identity.
     *
     * Linking precedence:
     *   1. existing account by thaid_sub (canonical)
     *   2. existing account by email — ONLY when DOPA marks the email verified
     *      (otherwise an attacker-set unverified email could hijack an account)
     *   3. create a fresh least-privilege (viewer) account
     *
     * @return array<string,mixed>
     * @throws \RuntimeException when the resolved account is inactive
     */
    private function resolveUser(ThaIdIdentityDto $identity): array
    {
        $user = User::findByThaidSub($identity->sub);

        if ($user === null && $identity->email !== '' && $identity->emailVerified) {
            $byEmail = User::findByEmail($identity->email);
            if ($byEmail !== null) {
                User::update((int) $byEmail['id'], ['thaid_sub' => $identity->sub]);
                $user = $byEmail;
            }
        }

        if ($user === null) {
            // Synthetic email is a schema placeholder only — thaid_sub is the
            // canonical identity; never treat this address as a real mailbox.
            $email = $identity->email !== ''
                ? $identity->email
                : $identity->sub . '@thaid.local';

            $userId = User::create([
                'email'     => $email,
                'password'  => bin2hex(random_bytes(16)), // random → no password login
                'name'      => $identity->nameTh !== '' ? $identity->nameTh : $identity->sub,
                'role'      => 'viewer',
                'thaid_sub' => $identity->sub,
            ]);
            $user = User::find($userId);
            if ($user === null) {
                throw new \RuntimeException('user_resolve_failed: post_create');
            }
        } else {
            $user = User::find((int) $user['id']);
            if ($user === null) {
                throw new \RuntimeException('user_resolve_failed: post_lookup');
            }
        }

        if (array_key_exists('is_active', $user) && !$user['is_active']) {
            throw new \RuntimeException('inactive');
        }

        return $user;
    }

    /** RFC 7636 base64url (no padding). */
    private static function base64Url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}
