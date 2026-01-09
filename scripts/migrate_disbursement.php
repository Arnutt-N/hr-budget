<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'vendor/autoload.php';

// Direct connection explicitly
$pdo = new PDO("mysql:host=localhost;dbname=hr_budget;charset=utf8mb4", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Starting Disbursement Form Migration ===\n";

$migrations = [
    '022_create_expense_types.sql',
    '023_create_disbursement_headers.sql',
    '024_create_disbursement_details.sql'
];

foreach ($migrations as $file) {
    echo "\n[Processing] $file...\n";
    try {
        $sql = file_get_contents("database/migrations/$file");
        if ($sql) {
            $pdo->exec($sql);
            echo "✅ Success: $file executed.\n";
        } else {
            echo "❌ Error: Could not read $file\n";
        }
    } catch (Exception $e) {
        echo "❌ Error executing $file: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Verify Tables ===\n";
$tables = ['expense_types', 'disbursement_headers', 'disbursement_details'];
foreach ($tables as $t) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $t");
        $count = $stmt->fetchColumn();
        echo "✅ Table '$t' exists. Rows: $count\n";
    } catch (Exception $e) {
        echo "❌ Table '$t' DOES NOT exist.\n";
    }
}
