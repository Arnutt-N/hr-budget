<?php
// scripts/check_schema.php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $db = \App\Core\Database::getInstance();
    $rows = \App\Core\Database::query("DESCRIBE disbursement_details");
    
    echo "Columns in disbursement_details:\n";
    $found = [];
    foreach ($rows as $row) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        $found[$row['Field']] = true;
    }
    
    $required = ['header_id', 'plan_id', 'output_id', 'activity_id', 'expense_type_id'];
    $missing = [];
    
    foreach ($required as $req) {
        if (!isset($found[$req])) {
            $missing[] = $req;
        }
    }
    
    if (!empty($missing)) {
        echo "\nMISSING COLUMNS: " . implode(', ', $missing) . "\n";
        exit(1);
    } else {
        echo "\nSCHEMA OK: All specific foreign keys present.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit(1);
}
