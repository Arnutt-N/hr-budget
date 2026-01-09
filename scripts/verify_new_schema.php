<?php
/**
 * Verify New Schema
 * ตรวจสอบว่า Migration สำเร็จและ Schema ถูกต้องตามที่กำหนด
 */

require_once __DIR__ . '/../vendor/autoload.php';

// FORCE USE 127.0.0.1 (Robust connection)
$host = '127.0.0.1';
$db   = 'hr_budget';
$user = 'root';
$pass = ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset;port=3306";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    echo "✅ Connected to Database\n\n";
    
    // 1. Check New Tables
    $newTables = ['dim_organization', 'dim_budget_structure', 'fact_budget_execution', 'log_transfer_note'];
    echo "🔍 Checking New Tables:\n";
    foreach ($newTables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "  ✅ Table '$table' exists\n";
        } else {
            echo "  ❌ Table '$table' NOT FOUND\n";
        }
    }
    echo "\n";

    // 2. Check NULLable fields (No Default 0)
    echo "🔍 Checking NULLable fields (Should be YES, Default NULL):\n";
    
    $checks = [
        'budgets' => ['allocated_amount', 'spent_amount'],
        'budget_allocations' => ['allocated_pba', 'disbursed'],
        'fact_budget_execution' => ['budget_act_amount', 'disbursed_amount']
    ];

    foreach ($checks as $table => $columns) {
        foreach ($columns as $col) {
            $stmt = $pdo->query("DESCRIBE `$table` `$col`");
            $info = $stmt->fetch();
            
            if ($info) {
                $isNullable = $info['Null'] === 'YES';
                $default = $info['Default'];
                
                $status = ($isNullable && $default === null) ? "✅ OK" : "❌ FAIL";
                $defaultDisplay = ($default === null) ? "NULL" : "'$default'";
                
                echo "  $status $table.$col | Null: {$info['Null']} | Default: $defaultDisplay\n";
            } else {
                echo "  ❌ Column $table.$col NOT FOUND\n";
            }
        }
    }

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
