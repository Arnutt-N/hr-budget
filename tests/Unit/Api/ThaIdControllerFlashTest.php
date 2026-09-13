<?php

declare(strict_types=1);

namespace Tests\Unit\Api;

use App\Api\Controllers\ThaIdController;
use App\Api\Responses\ApiResponse;
use App\Services\ThaIdConfig;
use PHPUnit\Framework\TestCase;

/**
 * One-time flash-error endpoint (the SPA login page's error path). flash() is
 * intentionally non-exiting so it can be asserted via the ApiResponse capture
 * statics, mirroring ThaIdControllerStatusTest.
 */
final class ThaIdControllerFlashTest extends TestCase
{
    protected function setUp(): void
    {
        ApiResponse::$lastBody = null;
        ApiResponse::$lastStatus = null;
        unset($_SESSION['flash_error']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['flash_error']);
    }

    public function test_flash_returns_message_then_consumes_it(): void
    {
        $_SESSION['flash_error'] = 'เข้าสู่ระบบด้วย ThaID ไม่สำเร็จ';
        $controller = new ThaIdController(new ThaIdConfig([]));

        ob_start();
        $controller->flash();
        ob_end_clean(); // swallow the echoed JSON; assert via capture statics

        $this->assertSame(200, ApiResponse::$lastStatus);
        $this->assertTrue(ApiResponse::$lastBody['success']);
        $this->assertSame('เข้าสู่ระบบด้วย ThaID ไม่สำเร็จ', ApiResponse::$lastBody['data']['message']);

        // Second read must be null — the flash is consumed on first read.
        ob_start();
        $controller->flash();
        ob_end_clean();

        $this->assertSame(200, ApiResponse::$lastStatus);
        $this->assertTrue(ApiResponse::$lastBody['success']);
        $this->assertNull(ApiResponse::$lastBody['data']['message']);
        $this->assertArrayNotHasKey('flash_error', $_SESSION);
    }

    public function test_flash_reports_null_message_when_no_flash_is_set(): void
    {
        ob_start();
        (new ThaIdController(new ThaIdConfig([])))->flash();
        ob_end_clean();

        $this->assertSame(200, ApiResponse::$lastStatus);
        $this->assertTrue(ApiResponse::$lastBody['success']);
        $this->assertNull(ApiResponse::$lastBody['data']['message']);
    }
}
