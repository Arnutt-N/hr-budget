<?php
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $tables = ['budget_allocations', 'disbursement_details'];
    $output = "";
    
    foreach ($tables as $table) {
        $output .= "Table: $table\n";
        try {
            $columns = Database::query("DESCRIBE $table");
            foreach ($columns as $col) {
                $output .= "{$col['Field']} - {$col['Type']}\n";
            }
        } catch (Exception $e) {
            $output .= "Error describing $table: " . $e->getMessage() . "\n";
        }
        $output .= "\n";
    }
    
    file_put_contents(__DIR__ . '/schema_dump.txt', $output);
    echo "Done writing to schema_dump.txt";

} catch (Exception $e) {
    file_put_contents(__DIR__ . '/schema_dump.txt', "Global Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
