<?php
/**
 * CORS Middleware for /api/* endpoints.
 *
 * Allows cross-origin browser requests from the Vue SPA dev server (:5174)
 * in development and configured origins in production.
 *
 * Short-circuits OPTIONS preflight with 204.
 */

namespace App\Api\Middleware;

final class CorsMiddleware
{
    /**
     * Apply CORS headers. MUST be called before any output.
     * Exits early on OPTIONS preflight.
     */
    public static function apply(): void
    {
        $cfg = require __DIR__ . '/../../../config/api.php';
        $allowedOrigins = $cfg['cors_origins'] ?? [];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin !== '' && in_array($origin, $allowedOrigins, true)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');
        header('Vary: Origin');

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
