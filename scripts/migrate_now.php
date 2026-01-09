<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'vendor/autoload.php';

use App\Core\Database;

// Direct connection explicitly
$pdo = new PDO("mysql:host=localhost;dbname=hr_budget;charset=utf8mb4", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Starting Migration ===\n";

// 1. Drop Dimensional Tables
echo "\n[Step 1] Running 017_drop_dimensional_tables.sql...\n";
try {
    $sql17 = file_get_contents('database/migrations/017_drop_dimensional_tables.sql');
    if ($sql17) {
        $pdo->exec($sql17);
        echo "✅ Success: 017_drop_dimensional_tables.sql executed.\n";
    } else {
        echo "❌ Error: Could not read 017_drop_dimensional_tables.sql\n";
    }
} catch (Exception $e) {
    echo "❌ Error executing 017: " . $e->getMessage() . "\n";
}

// 2. Enhance Organizations
echo "\n[Step 2] Running 018_enhance_organizations.sql...\n";
try {
    $sql18 = file_get_contents('database/migrations/018_enhance_organizations.sql');
    if ($sql18) {
        // Need to handle multiple statements if PDO doesn't support them well in one go, 
        // but typically exec() can handle it if emulation is on.
        // Let's split by ; just in case, or just try running it.
        $pdo->exec($sql18);
        echo "✅ Success: 018_enhance_organizations.sql executed.\n";
    } else {
        echo "❌ Error: Could not read 018_enhance_organizations.sql\n";
    }
} catch (Exception $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
         echo "⚠️ Warning: Columns already exist (Partial run?)\n";
    } else {
         echo "❌ Error executing 018: " . $e->getMessage() . "\n";
    }
}

// 3. Add hierarchy columns to budget_category_items
echo "\n[Step 3] Running 022_add_hierarchy_to_category_items.sql...\n";
try {
    $sql22 = file_get_contents('database/migrations/022_add_hierarchy_to_category_items.sql');
    if ($sql22) {
        $pdo->exec($sql22);
        echo "✅ Success: 022_add_hierarchy_to_category_items.sql executed.\n";
    } else {
        echo "❌ Error: Could not read 022_add_hierarchy_to_category_items.sql\n";
    }
} catch (Exception $e) {
    echo "❌ Error executing 022: " . $e->getMessage() . "\n";
}

echo "\n=== Verify Again ===\n";

// Check tables
$tables = ['dim_organization', 'organizations'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("DESCRIBE $t");
        echo "Table '$t' exists.\n";
        // List cols
        $cols = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Cols: " . implode(', ', array_slice($cols, 0, 10)) . "...\n";
    } catch (Exception $e) {
        echo "Table '$t' DOES NOT exist (Expected for dim_organization).\n";
    }
}
