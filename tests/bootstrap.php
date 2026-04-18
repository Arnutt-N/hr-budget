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

// Mirror phpunit.xml <env> values (which use putenv) into $_ENV so
// config files that read $_ENV (e.g. config/api.php, config/database.php) see them.
foreach (['JWT_SECRET', 'JWT_TTL', 'CORS_ORIGINS', 'APP_ENV', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
    if (!isset($_ENV[$key]) && ($val = getenv($key)) !== false) {
        $_ENV[$key] = $val;
    }
}

// config/database.php uses DB_DATABASE/DB_USERNAME/DB_PASSWORD naming but
// phpunit.xml uses short DB_NAME/DB_USER/DB_PASS. Bridge the gap.
$_ENV['DB_DATABASE'] = $_ENV['DB_DATABASE'] ?? $_ENV['DB_NAME'] ?? 'hr_budget_test';
$_ENV['DB_USERNAME'] = $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '';

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

// Initialize Auth system
use App\Core\Auth;
Auth::init();

// Start output buffering to prevent header issues
ob_start();
