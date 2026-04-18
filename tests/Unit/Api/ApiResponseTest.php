<?php

namespace Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use App\Api\Responses\ApiResponse;

class ApiResponseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ApiResponse::$lastStatus = null;
        ApiResponse::$lastBody = null;
    }

    /** @test */
    public function ok_returns_success_envelope_with_data(): void
    {
        ob_start();
        ApiResponse::ok(['foo' => 'bar'], exit: false);
        $raw = ob_get_clean();

        $this->assertSame(200, ApiResponse::$lastStatus);
        $this->assertSame(['success' => true, 'data' => ['foo' => 'bar']], ApiResponse::$lastBody);

        $decoded = json_decode($raw, true);
        $this->assertTrue($decoded['success']);
        $this->assertSame('bar', $decoded['data']['foo']);
    }

    /** @test */
    public function ok_includes_meta_when_provided(): void
    {
        ob_start();
        ApiResponse::ok(['a' => 1], meta: ['total' => 42], exit: false);
        ob_end_clean();

        $this->assertSame(42, ApiResponse::$lastBody['meta']['total']);
    }

    /** @test */
    public function error_returns_custom_status(): void
    {
        ob_start();
        ApiResponse::error('Boom', 418, null, false);
        ob_end_clean();

        $this->assertSame(418, ApiResponse::$lastStatus);
        $this->assertFalse(ApiResponse::$lastBody['success']);
        $this->assertSame('Boom', ApiResponse::$lastBody['error']);
    }

    /** @test */
    public function validation_failed_returns_422_with_details(): void
    {
        ob_start();
        ApiResponse::validationFailed(['email' => 'required'], exit: false);
        ob_end_clean();

        $this->assertSame(422, ApiResponse::$lastStatus);
        $this->assertSame('required', ApiResponse::$lastBody['details']['email']);
    }

    /** @test */
    public function unauthorized_returns_401(): void
    {
        ob_start();
        ApiResponse::unauthorized(exit: false);
        ob_end_clean();

        $this->assertSame(401, ApiResponse::$lastStatus);
    }

    /** @test */
    public function thai_text_is_not_escaped(): void
    {
        ob_start();
        ApiResponse::ok(['msg' => 'สวัสดี'], exit: false);
        $raw = ob_get_clean();

        $this->assertStringContainsString('สวัสดี', $raw);
        $this->assertStringNotContainsString('\\u0e2a', $raw);
    }
}
