<?php
/**
 * Check budget_plans table for encoding issues - CLI version
 * Output to file
 */

// Load config as array
$config = require __DIR__ . '/../config/database.php';

$output = "";
$output .= "=== budget_plans Encoding Check ===\n";
$output .= "Generated: " . date('Y-m-d H:i:s') . "\n";
$output .= str_repeat("=", 80) . "\n\n";

try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    $stmt = $pdo->query("SELECT id, code, name_th, plan_type, level FROM budget_plans ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $output .= "Total Records: " . count($rows) . "\n";
    $output .= str_repeat("-", 80) . "\n";
    $output .= sprintf("%-4s | %-25s | %-30s | %-10s\n", "ID", "Code", "Name (TH)", "Status");
    $output .= str_repeat("-", 80) . "\n";
    
    $problematic = [];
    
    foreach ($rows as $row) {
        // Check for encoding issues: question marks, double-encoded Thai, or mojibake
        $hasIssue = (preg_match('/\?{3,}/', $row['name_th']) || 
                     preg_match('/เธ|เน/', $row['name_th']));
        
        if ($hasIssue) $problematic[] = $row;
        
        $status = $hasIssue ? '*** ENCODING ISSUE ***' : 'OK';
        $name = mb_strlen($row['name_th']) > 30 ? mb_substr($row['name_th'], 0, 27) . '...' : $row['name_th'];
        
        $output .= sprintf("%-4s | %-25s | %-30s | %s\n",
            $row['id'],
            $row['code'],
            $name,
            $status
        );
    }
    
    $output .= str_repeat("-", 80) . "\n\n";
    
    if (count($problematic) > 0) {
        $output .= "=== PROBLEMATIC RECORDS (" . count($problematic) . ") ===\n";
        $output .= str_repeat("-", 80) . "\n";
        
        foreach ($problematic as $row) {
            $output .= "ID: {$row['id']}\n";
            $output .= "Code: {$row['code']}\n";
            $output .= "Name: {$row['name_th']}\n";
            $output .= "HEX: " . bin2hex($row['name_th']) . "\n";
            $output .= str_repeat("-", 40) . "\n";
        }
        
        $output .= "\nProblematic IDs: " . implode(', ', array_column($problematic, 'id')) . "\n";
    } else {
        $output .= "=== NO ENCODING ISSUES DETECTED ===\n";
    }
    
} catch (PDOException $e) {
    $output .= "ERROR: " . $e->getMessage() . "\n";
}

// Save to file
$outputFile = __DIR__ . '/../logs/encoding_check_' . date('Ymd_His') . '.txt';
file_put_contents($outputFile, $output);

echo "Output saved to: $outputFile\n";
echo $output;
