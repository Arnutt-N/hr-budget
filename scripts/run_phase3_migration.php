<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "=== Running Phase 3 Migration (011_phase3_refactoring.sql) ===\n";

$sqlFile = __DIR__ . '/../database/migrations/011_phase3_refactoring.sql';

if (!file_exists($sqlFile)) {
    die("❌ SQL file not found: $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

try {
    $db = Database::getPdo();
    $db->exec($sql);
    echo "✅ Migration executed successfully.\n";
} catch (PDOException $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
