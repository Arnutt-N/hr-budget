<?php
require_once __DIR__ . '/public/db-api.php';
use App\Core\Database;

echo "Columns in 'fact_budget_execution':\n";
$columns = Database::query("SHOW COLUMNS FROM fact_budget_execution");
foreach ($columns as $col) {
    echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
}
