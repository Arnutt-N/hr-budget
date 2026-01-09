<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

try {
    $db = Database::getPdo();
    $tables = ['folders', 'files'];
    $allExists = true;

    echo "Verifying File Management tables:\n";

    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            echo "✅ Table '$table' exists.\n";
            
            // Check columns for folders to ensure it has new schema
            if ($table === 'folders') {
                $cols = $db->query("DESCRIBE folders")->fetchAll(PDO::FETCH_COLUMN);
                if (in_array('fiscal_year', $cols) && in_array('budget_category_id', $cols)) {
                    echo "   - Schema check: OK (fiscal_year, budget_category_id found)\n";
                } else {
                    echo "   - Schema check: ❌ FAILED (Missing new columns)\n";
                    $allExists = false;
                }
            }
        } else {
            echo "❌ Table '$table' DOES NOT exist.\n";
            $allExists = false;
        }
    }

    if ($allExists) {
        echo "\n🎉 Verification SUCCESS: All tables are ready.\n";
    } else {
        echo "\n⚠️ Verification FAILED: Some tables or columns are missing.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
