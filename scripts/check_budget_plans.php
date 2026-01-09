<?php
/**
 * Check budget_plans table for encoding issues
 */

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query("SELECT id, code, name_th, plan_type, level FROM budget_plans ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== budget_plans table ===\n";
    echo str_repeat("-", 100) . "\n";
    echo sprintf("%-4s | %-20s | %-50s | %-10s | %s\n", "ID", "Code", "Name (TH)", "Type", "Level");
    echo str_repeat("-", 100) . "\n";
    
    $problematic = [];
    
    foreach ($rows as $row) {
        $name = mb_strlen($row['name_th']) > 50 ? mb_substr($row['name_th'], 0, 47) . '...' : $row['name_th'];
        echo sprintf("%-4s | %-20s | %-50s | %-10s | %s\n",
            $row['id'],
            $row['code'],
            $name,
            $row['plan_type'],
            $row['level']
        );
        
        // Check for encoding issues
        if (preg_match('/\?{3,}/', $row['name_th']) || 
            preg_match('/เธ|เน/', $row['name_th'])) {
            $problematic[] = $row;
        }
    }
    
    echo str_repeat("-", 100) . "\n";
    echo "Total records: " . count($rows) . "\n\n";
    
    if (count($problematic) > 0) {
        echo "=== PROBLEMATIC RECORDS (Encoding Issues) ===\n";
        echo str_repeat("-", 80) . "\n";
        foreach ($problematic as $row) {
            echo "ID: {$row['id']}, Code: {$row['code']}\n";
            echo "   Name: {$row['name_th']}\n";
            echo "   HEX: " . bin2hex($row['name_th']) . "\n";
            echo str_repeat("-", 80) . "\n";
        }
        echo "Problematic records: " . count($problematic) . "\n";
    } else {
        echo "No encoding issues detected!\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
