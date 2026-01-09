<?php
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $row = Database::queryOne("SELECT * FROM budget_allocations LIMIT 1");
    if ($row) {
        echo "Columns found:\n";
        print_r(array_keys($row));
    } else {
        echo "No data in budget_allocations. Using DESCRIBE:\n";
        $cols = Database::query("DESCRIBE budget_allocations");
        foreach ($cols as $c) {
            echo $c['Field'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
