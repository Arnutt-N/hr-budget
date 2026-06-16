<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Http\HttpClientInterface;
use App\Dtos\ThaIdIdentityDto;

/**
 * OAuth2 authorization-code adapter for ThaID (DOPA imauth.bora.dopa.go.th).
 *
 * Every DOPA-specific protocol detail (URLs, client-auth style, claim names)
 * is confined to this class and is config-driven, so the orchestrating service
 * and controller stay provider-agnostic and testable with a fake HttpClient.
 *
 * Thrown exceptions carry GENERIC, status-only messages — never the raw DOPA
 * response body, which can contain error_description / credential hints that
 * would then leak into error_log.
 */
final class ThaIdProvider
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly ThaIdConfig $cfg,
    ) {}

    /**
     * Build the authorize-endpoint URL the browser is redirected to.
     * $codeChallenge is the PKCE S256 challenge (null when PKCE is disabled).
     */
    public function authorizeUrl(string $state, ?string $codeChallenge): string
    {
        $params = [
            'response_type' => 'code',
            'client_id'     => $this->cfg->clientId(),
            'redirect_uri'  => $this->cfg->redirectUri(),
            'scope'         => $this->cfg->scope(),
            'state'         => $state,
        ];

        if ($this->cfg->pkce() && $codeChallenge !== null && $codeChallenge !== '') {
            $params['code_challenge']        = $codeChallenge;
            $params['code_challenge_method'] = 'S256';
        }

        $sep = str_contains($this->cfg->authorizeUrl(), '?') ? '&' : '?';
        // RFC 3986 encoding (space → %20) for the authorize *query string* so the
        // redirect_uri matches DOPA's registered value byte-for-byte. (The token
        // POST body, by contrast, is form-urlencoded with '+' — handled in curl.)
        return $this->cfg->authorizeUrl() . $sep . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Exchange the authorization code for tokens.
     *
     * @return array{access_token:string, id_token:?string}
     * @throws \RuntimeException on transport/HTTP error or missing access token
     */
    public function exchangeCode(string $code, ?string $codeVerifier): array
    {
        $form = [
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $this->cfg->redirectUri(),
        ];

        if ($this->cfg->pkce() && $codeVerifier !== null && $codeVerifier !== '') {
            $form['code_verifier'] = $codeVerifier;
        }

        $opts = [
            'headers' => ['Accept: application/json'],
            'form'    => $form,
        ];

        // client_secret_basic (DOPA norm) vs client_secret_post.
        if ($this->cfg->clientAuth() === 'post') {
            $opts['form']['client_id']     = $this->cfg->clientId();
            $opts['form']['client_secret'] = $this->cfg->clientSecret();
        } else {
            $opts['basic_auth'] = [$this->cfg->clientId(), $this->cfg->clientSecret()];
        }

        $resp = $this->http->request('POST', $this->cfg->tokenUrl(), $opts);
        if (!$resp->isOk()) {
            throw new \RuntimeException("token_exchange_failed: HTTP {$resp->status}");
        }

        $body = $resp->json();
        $token = (string) ($body['access_token'] ?? '');
        if ($token === '') {
            throw new \RuntimeException('token_exchange_failed: no access_token in response');
        }

        $idToken = isset($body['id_token']) && $body['id_token'] !== ''
            ? (string) $body['id_token']
            : null;

        return ['access_token' => $token, 'id_token' => $idToken];
    }

    /**
     * Verify a DOPA-signed id_token against the configured JWKS (defense-in-
     * depth on top of the TLS-authenticated userinfo call). Confirms signature
     * + expiry (via JWT::decode), then issuer/audience when configured.
     *
     * @return array<string,mixed> the verified claims
     * @throws \RuntimeException on fetch/parse/signature/claim failure
     */
    public function verifyIdToken(string $idToken): array
    {
        $resp = $this->http->request('GET', $this->cfg->jwksUrl(), [
            'headers' => ['Accept: application/json'],
        ]);
        if (!$resp->isOk()) {
            throw new \RuntimeException("jwks_fetch_failed: HTTP {$resp->status}");
        }

        try {
            $keys = \Firebase\JWT\JWK::parseKeySet($resp->json());
            $claims = (array) \Firebase\JWT\JWT::decode($idToken, $keys);
        } catch (\Throwable $e) {
            // Generic message — never echo the token or raw exception detail.
            throw new \RuntimeException('id_token_invalid: ' . $e::class);
        }

        $iss = $this->cfg->issuer();
        if ($iss !== '' && (string) ($claims['iss'] ?? '') !== $iss) {
            throw new \RuntimeException('id_token_iss_mismatch');
        }

        $aud = $this->cfg->audience();
        if ($aud !== '') {
            $tokenAud = $claims['aud'] ?? '';
            $auds = is_array($tokenAud) ? array_map('strval', $tokenAud) : [(string) $tokenAud];
            if (!in_array($aud, $auds, true)) {
                throw new \RuntimeException('id_token_aud_mismatch');
            }
        }

        return $claims;
    }

    /**
     * Fetch + normalize the user identity from the userinfo endpoint.
     *
     * @throws \RuntimeException on transport/HTTP error or missing subject
     */
    public function fetchUserInfo(string $accessToken): ThaIdIdentityDto
    {
        $resp = $this->http->request('GET', $this->cfg->userinfoUrl(), [
            'headers' => [
                'Accept: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
        ]);

        if (!$resp->isOk()) {
            throw new \RuntimeException("userinfo_failed: HTTP {$resp->status}");
        }

        $identity = ThaIdIdentityDto::fromUserInfo($resp->json(), $this->cfg->fieldMap());
        if ($identity->sub === '') {
            throw new \RuntimeException('userinfo_failed: missing subject claim');
        }
        // thaid_sub is VARCHAR(64); reject an over-long subject rather than risk
        // a DB truncation that would split one identity across two rows.
        if (strlen($identity->sub) > 64) {
            throw new \RuntimeException('userinfo_failed: subject claim too long');
        }

        return $identity;
    }
}
