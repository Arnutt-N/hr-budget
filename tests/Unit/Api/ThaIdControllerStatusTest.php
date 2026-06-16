<?php

declare(strict_types=1);

namespace Tests\Unit\Api;

use App\Api\Controllers\ThaIdController;
use App\Api\Responses\ApiResponse;
use App\Services\ThaIdConfig;
use PHPUnit\Framework\TestCase;

/**
 * Cheap controller coverage for the public status endpoint (the SPA's gate).
 * status() is intentionally non-exiting so it can be asserted via the
 * ApiResponse capture statics.
 */
final class ThaIdControllerStatusTest extends TestCase
{
    protected function setUp(): void
    {
        ApiResponse::$lastBody = null;
        ApiResponse::$lastStatus = null;
    }

    public function test_status_reports_enabled_when_credentials_present(): void
    {
        $cfg = new ThaIdConfig([
            'mock' => false, 'client_id' => 'c', 'client_secret' => 's',
            'redirect_uri' => 'https://app/cb',
        ]);

        ob_start();
        (new ThaIdController($cfg))->status();
        ob_end_clean(); // swallow the echoed JSON; assert via capture statics

        $this->assertSame(200, ApiResponse::$lastStatus);
        $this->assertTrue(ApiResponse::$lastBody['success']);
        $this->assertTrue(ApiResponse::$lastBody['data']['enabled']);
        $this->assertFalse(ApiResponse::$lastBody['data']['mock']);
    }

    public function test_status_reports_dormant_when_unconfigured(): void
    {
        ob_start();
        (new ThaIdController(new ThaIdConfig([])))->status();
        ob_end_clean();

        $this->assertSame(200, ApiResponse::$lastStatus);
        $this->assertFalse(ApiResponse::$lastBody['data']['enabled']);
        $this->assertFalse(ApiResponse::$lastBody['data']['mock']);
    }
}
