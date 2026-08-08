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

// Load .env so optional local settings (mail, timezone, ...) are available.
// NOTE: .env points at the *development* database. Everything DB-related below
// deliberately overrides it — see the override loop.
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Default the test database name when phpunit.xml did not supply one.
if (getenv('DB_NAME') === false) {
    putenv('DB_NAME=hr_budget_test');
}

// Mirror phpunit.xml <env> values (which use putenv) into $_ENV so config files
// that read $_ENV (e.g. config/api.php, config/database.php) see them.
//
// These values OVERRIDE anything Dotenv just loaded. Without the override, a
// local .env with DB_DATABASE=hr_budget silently pointed the whole suite at the
// real development database — tests that create/delete rows then mutated live
// data while still reporting green.
foreach (['JWT_SECRET', 'JWT_TTL', 'CORS_ORIGINS', 'APP_ENV', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $key) {
    if (($val = getenv($key)) !== false) {
        $_ENV[$key] = $val;
    }
}

// config/database.php uses DB_DATABASE/DB_USERNAME/DB_PASSWORD naming but
// phpunit.xml uses short DB_NAME/DB_USER/DB_PASS. Bridge the gap.
// Assign unconditionally: the short names are the single source of truth here.
$_ENV['DB_DATABASE'] = $_ENV['DB_NAME'] ?? 'hr_budget_test';
$_ENV['DB_USERNAME'] = $_ENV['DB_USER'] ?? 'root';
$_ENV['DB_PASSWORD'] = $_ENV['DB_PASS'] ?? '';

// Fail fast if the suite is somehow aimed at a non-test database. Tests
// truncate and insert freely, so running them against the dev/production
// database would destroy real data.
if (!str_ends_with($_ENV['DB_DATABASE'], '_test')) {
    fwrite(STDERR, sprintf(
        "\nREFUSING TO RUN: test suite is pointed at database '%s', which is not a *_test database.\n"
        . "Check phpunit.xml <env name=\"DB_NAME\"> and any DB_NAME environment variable.\n\n",
        $_ENV['DB_DATABASE']
    ));
    exit(1);
}

// Set timezone
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');

// Initialize Auth system
use App\Core\Auth;
Auth::init();

// Start output buffering to prevent header issues
ob_start();
