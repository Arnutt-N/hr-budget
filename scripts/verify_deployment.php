<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Organization;
use App\Core\Database;

$errors = [];
$success = [];

echo "Running Verification...\n";

// 1. Verify Division files are gone
$filesToDelete = [
    'src/Models/Division.php',
    'src/Controllers/DivisionController.php',
    'resources/views/admin/divisions/index.php',
    'resources/views/admin/divisions/form.php'
];

foreach ($filesToDelete as $file) {
    if (file_exists(__DIR__ . '/../' . $file)) {
        $errors[] = "File still exists: $file";
    } else {
        $success[] = "File deleted: $file";
    }
}

// 2. Verify Organizations Table Schema
try {
    $stmt = Database::getInstance()->query("DESCRIBE organizations");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $requiredCols = ['org_type', 'province_code', 'region', 'contact_phone', 'contact_email', 'address'];
    
    foreach ($requiredCols as $col) {
        if (in_array($col, $columns)) {
            $success[] = "Column exists: $col";
        } else {
            $errors[] = "Missing column: $col";
        }
    }
} catch (Exception $e) {
    $errors[] = "DB Error: " . $e->getMessage();
}

// 3. Verify V_ORGANIZATIONS_HIERARCHY view
try {
    $stmt = Database::getInstance()->query("SELECT 1 FROM v_organizations_hierarchy LIMIT 1");
    $success[] = "View v_organizations_hierarchy exists";
} catch (Exception $e) {
    $errors[] = "View v_organizations_hierarchy missing or error: " . $e->getMessage();
}

// Report
echo "\n--- Successes ---\n";
foreach ($success as $msg) echo "✅ $msg\n";

echo "\n--- Errors ---\n";
if (empty($errors)) {
    echo "None! All checks passed.\n";
} else {
    foreach ($errors as $msg) echo "❌ $msg\n";
}
