<?php
/**
 * HR Budget Management System
 * 
 * Application Entry Point
 */

// Set error reporting based on environment
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Display errors in development
if (($_ENV['APP_DEBUG'] ?? false) === 'true') {
    ini_set('display_errors', 1);
}

// Initialize authentication
\App\Core\Auth::init();

// Load routes
require BASE_PATH . '/routes/web.php';

// Dispatch request
\App\Core\Router::dispatch();
