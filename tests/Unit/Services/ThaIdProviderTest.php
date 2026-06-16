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

        $token = $provider->exchangeCode('AUTHCODE', 'VERIFIER');

        $this->assertSame('AT-999', $token);
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
}
