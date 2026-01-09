<?php
require_once __DIR__ . '/src/Core/Database.php';
require_once __DIR__ . '/config/database.php';

try {
    $db = \App\Core\Database::getInstance();
    $sqlContent = file_get_contents(__DIR__ . '/database/migrations/036_update_budget_trackings_references.sql');
    
    // Split by semicolon but respect basic SQL structure (naive split is usually fine for migrations)
    $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
    
    foreach ($statements as $sql) {
        if (empty($sql)) continue;
        echo "Executing: " . substr($sql, 0, 50) . "...\n";
        try {
            $db->query($sql);
            echo "Success\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage();
}
