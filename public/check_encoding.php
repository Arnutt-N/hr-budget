<?php
/**
 * Check budget_plans table for encoding issues via web
 */
header('Content-Type: text/html; charset=utf-8');

// Load config as array
$config = require __DIR__ . '/../config/database.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Check Encoding</title>";
echo "<style>
body { font-family: Consolas, monospace; font-size: 12px; background: #1e1e1e; color: #ddd; padding: 20px; }
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #444; padding: 5px; text-align: left; }
th { background: #333; }
tr:nth-child(even) { background: #2a2a2a; }
.error { color: #f33; background: #511; }
.ok { color: #3f3; }
h2 { color: #0af; }
</style></head><body>";

try {
    $dsn = sprintf(
        "mysql:host=%s;port=%s;dbname=%s;charset=%s",
        $config['host'],
        $config['port'],
        $config['database'],
        $config['charset']
    );
    
    $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
    
    $stmt = $pdo->query("SELECT id, code, name_th, plan_type, level FROM budget_plans ORDER BY id");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>budget_plans table (" . count($rows) . " records)</h2>";
    
    echo "<table><tr><th>ID</th><th>Code</th><th>Name (TH)</th><th>Type</th><th>Level</th><th>Status</th></tr>";
    
    $problematic = [];
    
    foreach ($rows as $row) {
        // Check for encoding issues: question marks, double-encoded Thai, or mojibake
        $hasIssue = (preg_match('/\?{3,}/', $row['name_th']) || 
                     preg_match('/เธ|เน/', $row['name_th']));
        
        if ($hasIssue) $problematic[] = $row;
        
        $rowClass = $hasIssue ? 'error' : '';
        $status = $hasIssue ? '❌ Encoding Issue' : '✅ OK';
        
        echo "<tr class='$rowClass'>";
        echo "<td>{$row['id']}</td>";
        echo "<td>" . htmlspecialchars($row['code']) . "</td>";
        echo "<td>" . htmlspecialchars($row['name_th']) . "</td>";
        echo "<td>{$row['plan_type']}</td>";
        echo "<td>{$row['level']}</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    if (count($problematic) > 0) {
        echo "<h2 style='color:#f66'>⚠️ Problematic Records: " . count($problematic) . "</h2>";
        echo "<p>These records have encoding issues (? characters or double-encoded Thai):</p>";
        echo "<table><tr><th>ID</th><th>Code</th><th>Raw Name</th><th>HEX Value</th></tr>";
        foreach ($problematic as $row) {
            echo "<tr class='error'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>" . htmlspecialchars($row['code']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name_th']) . "</td>";
            echo "<td><pre style='margin:0;font-size:10px'>" . chunk_split(bin2hex($row['name_th']), 50, "\n") . "</pre></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<h2 class='ok'>✅ No encoding issues detected!</h2>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color:red'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "</body></html>";
