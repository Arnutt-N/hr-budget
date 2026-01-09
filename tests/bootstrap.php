<?php
/**
 * PHPUnit Bootstrap File
 * Initializes the testing environment
 */

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('TESTING', true);

// Autoload Composer packages
require BASE_PATH . '/vendor/autoload.php';

// Load test environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Override with test database if env vars exist
if (getenv('DB_NAME') === false) {
    putenv('DB_NAME=hr_budget_test');
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

// Initialize Auth system
use App\Core\Auth;
Auth::init();

// Start output buffering to prevent header issues
ob_start();
