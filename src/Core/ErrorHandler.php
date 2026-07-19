<?php

namespace App\Core;

use App\Core\Auth;
use Exception;
use Throwable;

class ErrorHandler
{
    /**
     * Register global error handlers
     */
    public static function register(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
    }

    /**
     * Convert PHP errors to Exceptions.
     *
     * Returns true to swallow the error when it was suppressed with `@`
     * (error_reporting() is 0 in that case) — otherwise PHP would fall through
     * to its default handler, which is inconsistent with this handler's job of
     * converting everything to an exception.
     */
    public static function handleError($level, $message, $file = null, $line = null): bool
    {
        // `@`-suppressed errors: error_reporting() returns 0. Swallow them so
        // PHP does not also invoke its default handler (which would print the
        // message despite the @).
        if ((error_reporting() & $level) === 0) {
            return true;
        }
        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    /**
     * Handle uncaught exceptions
     */
    public static function handleException(Throwable $exception): void
    {
        // Log the error
        self::log($exception);

        // Check if it's an API request
        $isApi = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
            (strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false);

        // Clean output
        if (ob_get_length()) {
            ob_clean();
        }

        // No HttpException type exists in this codebase — every uncaught
        // exception is treated as a 500. (Previous code checked an
        // \App\Exceptions\HttpException class that was never defined.)
        $code = 500;

        http_response_code($code);

        if ($isApi) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => true,
                'message' => self::getErrorMessage($exception)
            ]);
        } else {
            // Render standalone error page (no layout — see View::render).
            if ($code >= 500) {
                View::render('errors/500', ['exception' => $exception]);
            } else {
                View::render("errors/{$code}", ['exception' => $exception]);
            }
        }
    }

    /**
     * Log exception to database. Falls back to PHP's error_log when the
     * `error_logs` table is missing (notably in CI, where the snapshot in
     * `database/hr_budget_only.sql` does not include it — see
     * `scripts/migrate_error_logs.php` for the production bootstrap).
     */
    public static function log(Throwable $exception): void
    {
        try {
            $userId = Auth::check() ? Auth::id() : null;
            $url = $_SERVER['REQUEST_URI'] ?? 'CLI';
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;

            Database::insert('error_logs', [
                'error_type' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'url' => $url,
                'user_id' => $userId,
                'ip_address' => $ip
            ]);
        } catch (Throwable $e) {
            // Fallback to file log if database fails (missing table, connection
            // down, etc.). Always also log the original exception so it is not
            // silently lost when the DB write fails.
            error_log("[ErrorHandler] DB log failed: " . $e->getMessage());
            error_log("[ErrorHandler] Original exception: " . $exception->getMessage()
                . " in " . $exception->getFile() . ":" . $exception->getLine());
        }
    }

    /**
     * Get user-friendly error message based on environment
     */
    private static function getErrorMessage(Throwable $e): string
    {
        $showDetails = ($_ENV['APP_DEBUG'] ?? 'false') === 'true';
        
        if ($showDetails) {
            return $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        }

        return 'เกิดข้อผิดพลาดภายในระบบ กรุณาลองใหม่อีกครั้ง';
    }
}
