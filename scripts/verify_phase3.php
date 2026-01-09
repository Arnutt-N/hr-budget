<?php
require_once __DIR__ . '/../public/index.php'; // Use simple include if possible, but index might dispatch.
// Actually lets just use raw PDO with config

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

use App\Core\Database;

try {
    $tables = Database::query("SHOW TABLES LIKE 'budget_requests'");
    if (count($tables) > 0) {
        echo "✅ Table 'budget_requests' exists.\n";
    } else {
        echo "❌ Table 'budget_requests' NOT FOUND.\n";
    }

    $tables2 = Database::query("SHOW TABLES LIKE 'budget_request_approvals'");
    if (count($tables2) > 0) {
        echo "✅ Table 'budget_request_approvals' exists.\n";
    } else {
        echo "❌ Table 'budget_request_approvals' NOT FOUND.\n";
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
