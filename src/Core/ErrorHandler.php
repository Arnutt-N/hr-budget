<?php

namespace App\Core;

use App\Models\Auth;
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
     * Convert PHP errors to Exceptions
     */
    public static function handleError($level, $message, $file = null, $line = null): bool
    {
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
        return false;
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

        $code = 500;
        if ($exception instanceof \App\Exceptions\HttpException) {
            $code = $exception->getCode();
        }
        
        http_response_code($code);

        if ($isApi) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => true,
                'message' => self::getErrorMessage($exception)
            ]);
        } else {
            // Render error page
            if ($code >= 500) {
                 View::render('errors/500', ['exception' => $exception], 'error');
            } else {
                 View::render("errors/{$code}", ['exception' => $exception], 'error');
            }
        }
    }

    /**
     * Log exception to database
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
            // Fallback to file log if database fails
            error_log("Failed to log error to DB: " . $e->getMessage());
            error_log($exception);
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
