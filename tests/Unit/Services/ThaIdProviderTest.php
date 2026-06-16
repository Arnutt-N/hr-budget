<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Http\HttpResponse;
use App\Services\ThaIdConfig;
use App\Services\ThaIdProvider;
use PHPUnit\Framework\TestCase;
use Tests\Support\FakeHttpClient;

/**
 * The OAuth2 protocol adapter — exercised entirely against a FakeHttpClient
 * (no real DOPA network). Verifies request shape and that thrown errors are
 * generic (no leaked response bodies).
 */
final class ThaIdProviderTest extends TestCase
{
    private function config(array $overrides = []): ThaIdConfig
    {
        return new ThaIdConfig(array_merge([
            'mock'          => false,
            'client_id'     => 'CID',
            'client_secret' => 'SECRET',
            'redirect_uri'  => 'https://app.example/cb',
            'authorize_url' => 'https://idp.example/auth',
            'token_url'     => 'https://idp.example/token',
            'userinfo_url'  => 'https://idp.example/userinfo',
            'scope'         => 'pid name',
            'pkce'          => true,
            'client_auth'   => 'basic',
        ], $overrides));
    }

    public function test_authorize_url_contains_oauth_and_pkce_params(): void
    {
        $provider = new ThaIdProvider(new FakeHttpClient([]), $this->config());
        $url = $provider->authorizeUrl('STATE123', 'CHALLENGE456');

        $this->assertStringStartsWith('https://idp.example/auth?', $url);
        $this->assertStringContainsString('response_type=code', $url);
        $this->assertStringContainsString('client_id=CID', $url);
        $this->assertStringContainsString('redirect_uri=' . rawurlencode('https://app.example/cb'), $url);
        $this->assertStringContainsString('scope=' . rawurlencode('pid name'), $url);
        $this->assertStringContainsString('state=STATE123', $url);
        $this->assertStringContainsString('code_challenge=CHALLENGE456', $url);
        $this->assertStringContainsString('code_challenge_method=S256', $url);
    }

    public function test_authorize_url_omits_pkce_when_disabled(): void
    {
        $provider = new ThaIdProvider(new FakeHttpClient([]), $this->config(['pkce' => false]));
        $url = $provider->authorizeUrl('S', null);
        $this->assertStringNotContainsString('code_challenge', $url);
    }

    public function test_exchange_code_returns_access_token_with_basic_auth(): void
    {
        $http = new FakeHttpClient([new HttpResponse(200, '{"access_token":"AT-999","token_type":"Bearer"}')]);
        $provider = new ThaIdProvider($http, $this->config());

        $result = $provider->exchangeCode('AUTHCODE', 'VERIFIER');

        $this->assertSame('AT-999', $result['access_token']);
        $this->assertNull($result['id_token']); // none in this response
        $req = $http->requests[0];
        $this->assertSame('POST', $req['method']);
        $this->assertSame('https://idp.example/token', $req['url']);
        $this->assertSame(['CID', 'SECRET'], $req['opts']['basic_auth']);
        $this->assertSame('authorization_code', $req['opts']['form']['grant_type']);
        $this->assertSame('AUTHCODE', $req['opts']['form']['code']);
        $this->assertSame('VERIFIER', $req['opts']['form']['code_verifier']);
    }

    public function test_exchange_code_post_auth_puts_secret_in_body(): void
    {
        $http = new FakeHttpClient([new HttpResponse(200, '{"access_token":"AT"}')]);
        $provider = new ThaIdProvider($http, $this->config(['client_auth' => 'post']));

        $provider->exchangeCode('C', 'V');

        $req = $http->requests[0];
        $this->assertArrayNotHasKey('basic_auth', $req['opts']);
        $this->assertSame('CID', $req['opts']['form']['client_id']);
        $this->assertSame('SECRET', $req['opts']['form']['client_secret']);
    }

    public function test_exchange_code_throws_generic_message_on_error(): void
    {
        $http = new FakeHttpClient([new HttpResponse(400, '{"error":"invalid_grant","error_description":"leak-secret-hint"}')]);
        $provider = new ThaIdProvider($http, $this->config());

        try {
            $provider->exchangeCode('C', 'V');
            $this->fail('expected RuntimeException');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('token_exchange_failed', $e->getMessage());
            $this->assertStringNotContainsString('leak-secret-hint', $e->getMessage());
        }
    }

    public function test_exchange_code_throws_when_no_token_in_body(): void
    {
        $http = new FakeHttpClient([new HttpResponse(200, '{"token_type":"Bearer"}')]);
        $provider = new ThaIdProvider($http, $this->config());

        $this->expectException(\RuntimeException::class);
        $provider->exchangeCode('C', 'V');
    }

    public function test_fetch_user_info_normalizes_identity_and_sends_bearer(): void
    {
        $http = new FakeHttpClient([new HttpResponse(200, '{"sub":"PID-1","name":"ชื่อ ทดสอบ","email":"a@b.com","email_verified":true}')]);
        $provider = new ThaIdProvider($http, $this->config());

        $identity = $provider->fetchUserInfo('AT-999');

        $this->assertSame('PID-1', $identity->sub);
        $this->assertSame('ชื่อ ทดสอบ', $identity->nameTh);
        $this->assertSame('a@b.com', $identity->email);
        $this->assertTrue($identity->emailVerified);
        $this->assertContains('Authorization: Bearer AT-999', $http->requests[0]['opts']['headers']);
    }

    public function test_fetch_user_info_throws_when_subject_missing(): void
    {
        $http = new FakeHttpClient([new HttpResponse(200, '{"name":"no sub"}')]);
        $provider = new ThaIdProvider($http, $this->config());

        $this->expectException(\RuntimeException::class);
        $provider->fetchUserInfo('AT');
    }

    public function test_fetch_user_info_throws_on_http_error(): void
    {
        $http = new FakeHttpClient([new HttpResponse(401, 'unauthorized')]);
        $provider = new ThaIdProvider($http, $this->config());

        $this->expectException(\RuntimeException::class);
        $provider->fetchUserInfo('AT');
    }

    public function test_exchange_code_parses_id_token_when_present(): void
    {
        $http = new FakeHttpClient([new HttpResponse(200, '{"access_token":"AT","id_token":"ID.JWT.SIG"}')]);
        $result = (new ThaIdProvider($http, $this->config()))->exchangeCode('C', 'V');
        $this->assertSame('ID.JWT.SIG', $result['id_token']);
    }

    public function test_verify_id_token_accepts_a_correctly_signed_token(): void
    {
        [$pem, $jwks, $kid] = $this->rsaKeyAndJwks('kid-A');
        $idToken = \Firebase\JWT\JWT::encode(
            ['iss' => 'https://idp', 'aud' => 'CID', 'sub' => 'PID-1', 'exp' => time() + 3600],
            $pem, 'RS256', $kid,
        );
        $http = new FakeHttpClient([new HttpResponse(200, (string) json_encode($jwks))]);
        $provider = new ThaIdProvider($http, $this->config([
            'jwks_url' => 'https://idp/jwks', 'issuer' => 'https://idp', 'audience' => 'CID',
        ]));

        $claims = $provider->verifyIdToken($idToken);
        $this->assertSame('PID-1', $claims['sub']);
    }

    public function test_verify_id_token_rejects_token_signed_by_another_key(): void
    {
        [, $jwks] = $this->rsaKeyAndJwks('kid-A');             // keyset has key A
        [$otherPem, , $otherKid] = $this->rsaKeyAndJwks('kid-B'); // token signed by key B
        $idToken = \Firebase\JWT\JWT::encode(['sub' => 'X', 'exp' => time() + 3600], $otherPem, 'RS256', $otherKid);
        $http = new FakeHttpClient([new HttpResponse(200, (string) json_encode($jwks))]);
        $provider = new ThaIdProvider($http, $this->config(['jwks_url' => 'https://idp/jwks']));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('id_token_invalid');
        $provider->verifyIdToken($idToken);
    }

    public function test_verify_id_token_rejects_issuer_mismatch(): void
    {
        [$pem, $jwks, $kid] = $this->rsaKeyAndJwks('kid-A');
        $idToken = \Firebase\JWT\JWT::encode(
            ['iss' => 'https://evil', 'aud' => 'CID', 'sub' => 'X', 'exp' => time() + 3600],
            $pem, 'RS256', $kid,
        );
        $http = new FakeHttpClient([new HttpResponse(200, (string) json_encode($jwks))]);
        $provider = new ThaIdProvider($http, $this->config([
            'jwks_url' => 'https://idp/jwks', 'issuer' => 'https://idp',
        ]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('id_token_iss_mismatch');
        $provider->verifyIdToken($idToken);
    }

    public function test_verify_id_token_rejects_audience_mismatch(): void
    {
        [$pem, $jwks, $kid] = $this->rsaKeyAndJwks('kid-A');
        $idToken = \Firebase\JWT\JWT::encode(
            ['aud' => 'SOMEONE-ELSE', 'sub' => 'X', 'exp' => time() + 3600],
            $pem, 'RS256', $kid,
        );
        $http = new FakeHttpClient([new HttpResponse(200, (string) json_encode($jwks))]);
        // audience() falls back to client_id 'CID'
        $provider = new ThaIdProvider($http, $this->config(['jwks_url' => 'https://idp/jwks']));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('id_token_aud_mismatch');
        $provider->verifyIdToken($idToken);
    }

    public function test_verify_id_token_throws_when_jwks_unreachable(): void
    {
        $http = new FakeHttpClient([new HttpResponse(503, '')]);
        $provider = new ThaIdProvider($http, $this->config(['jwks_url' => 'https://idp/jwks']));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('jwks_fetch_failed');
        $provider->verifyIdToken('any.jwt.here');
    }

    /**
     * Generate an RSA keypair + a single-key JWKS for it (RS256).
     *
     * @return array{0:string,1:array<string,mixed>,2:string} [privatePem, jwks, kid]
     */
    private function rsaKeyAndJwks(string $kid): array
    {
        $base = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];

        // Default config works on Linux/CI. On Windows, openssl_pkey_new needs an
        // openssl.cnf — try a couple of known Laragon locations before skipping,
        // so the crypto path still runs locally where possible.
        $res = @openssl_pkey_new($base);
        if ($res === false) {
            foreach ($this->opensslConfigCandidates() as $cnf) {
                $res = @openssl_pkey_new($base + ['config' => $cnf]);
                if ($res !== false) {
                    $exportCfg = ['config' => $cnf];
                    break;
                }
            }
        }
        if ($res === false) {
            $this->markTestSkipped('openssl_pkey_new unavailable (no usable openssl.cnf in this environment)');
        }

        openssl_pkey_export($res, $privatePem, null, $exportCfg ?? []);
        $details = openssl_pkey_get_details($res);

        $b64u = static fn (string $bin): string => rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
        $jwks = ['keys' => [[
            'kty' => 'RSA', 'use' => 'sig', 'alg' => 'RS256', 'kid' => $kid,
            'n' => $b64u($details['rsa']['n']), 'e' => $b64u($details['rsa']['e']),
        ]]];

        return [$privatePem, $jwks, $kid];
    }

    /** @return list<string> candidate openssl.cnf paths for Windows dev. */
    private function opensslConfigCandidates(): array
    {
        $found = [];
        foreach ([getenv('OPENSSL_CONF') ?: null] as $env) {
            if ($env !== null && is_file($env)) {
                $found[] = $env;
            }
        }
        foreach (glob('D:/laragon/bin/php/*/extras/ssl/openssl.cnf') ?: [] as $p) {
            $found[] = $p;
        }
        return $found;
    }
}
