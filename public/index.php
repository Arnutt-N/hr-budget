<?php
/**
 * HR Budget Management System
 * 
 * Application Entry Point
 */

// Report errors into PHP's log, but do NOT display them to end users.
// APP_DEBUG (read after .env loads below) can opt-in to on-screen display.
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Define base path
define('BASE_PATH', dirname(__DIR__));

// Define base URL for the application
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('BASE_URL', $protocol . '://' . $host . '/hr_budget/public');

// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Register Error Handler
App\Core\ErrorHandler::register();

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

// Opt-in to on-screen errors ONLY when explicitly in debug mode.
// Accept common truthy forms ("true", "1") from .env.
$__debug = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN);
if ($__debug) {
    ini_set('display_errors', '1');
}

// Apply CORS middleware for /api/* requests (must run BEFORE Auth::init
// because Auth starts a session and sends Set-Cookie which complicates preflight).
$__requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';
if (str_contains($__requestPath, '/api/')) {
    \App\Api\Middleware\CorsMiddleware::apply();
}

// Initialize authentication
\App\Core\Auth::init();

// Load routes
require BASE_PATH . '/routes/web.php';

// Dispatch request
\App\Core\Router::dispatch();
