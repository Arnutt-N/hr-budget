<?php
/**
 * API Response Envelope
 *
 * Consistent JSON response shape for all /api/v1/* endpoints:
 *   success -> { success: true,  data: <payload>, meta?: {...} }
 *   error   -> { success: false, error: <message>, details?: {...} }
 *
 * Every method sets HTTP status + Content-Type + prints JSON + optionally exits.
 *
 * Testability: pass $exit=false so PHPUnit can capture output without
 * killing the test runner. Default true keeps production code safe.
 */

namespace App\Api\Responses;

final class ApiResponse
{
    /** Last sent body — exposed for tests */
    public static ?array $lastBody = null;
    public static ?int $lastStatus = null;

    public static function ok(mixed $data = null, array $meta = [], bool $exit = true): void
    {
        $body = ['success' => true, 'data' => $data];
        if ($meta !== []) {
            $body['meta'] = $meta;
        }
        self::send(200, $body, $exit);
    }

    public static function created(mixed $data = null, bool $exit = true): void
    {
        self::send(201, ['success' => true, 'data' => $data], $exit);
    }

    public static function noContent(bool $exit = true): void
    {
        self::$lastStatus = 204;
        self::$lastBody = null;
        if (!headers_sent()) {
            http_response_code(204);
        }
        if ($exit) {
            exit;
        }
    }

    public static function error(string $message, int $status = 400, ?array $details = null, bool $exit = true): void
    {
        $body = ['success' => false, 'error' => $message];
        if ($details !== null) {
            $body['details'] = $details;
        }
        self::send($status, $body, $exit);
    }

    public static function unauthorized(string $message = 'Unauthorized', bool $exit = true): void
    {
        self::error($message, 401, null, $exit);
    }

    public static function forbidden(string $message = 'Forbidden', bool $exit = true): void
    {
        self::error($message, 403, null, $exit);
    }

    public static function notFound(string $message = 'Not found', bool $exit = true): void
    {
        self::error($message, 404, null, $exit);
    }

    public static function validationFailed(array $details, string $message = 'Validation failed', bool $exit = true): void
    {
        self::error($message, 422, $details, $exit);
    }

    /** @param array<string,mixed> $body */
    private static function send(int $status, array $body, bool $exit): void
    {
        self::$lastStatus = $status;
        self::$lastBody = $body;

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=UTF-8');
        }
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($exit) {
            exit;
        }
    }
}
