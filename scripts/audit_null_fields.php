<?php
/**
 * Audit Database NULL Fields
 * ตรวจสอบโครงสร้าง database และระบุ field ที่ควรเป็น NULL
 * 
 * หลักการ: ห้ามเดาค่าเอง ถ้า field ว่างต้องเป็น NULL
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "==============================================\n";
echo "  DATABASE NULL FIELDS AUDIT\n";
echo "  ตรวจสอบ Field ที่ควรเป็น NULL\n";
echo "==============================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Get all tables
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "📋 รายการตาราง: " . count($tables) . " ตาราง\n\n";
    
    $issues = [];
    
    foreach ($tables as $table) {
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "📁 ตาราง: {$table}\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        
        // Get column info
        $columns = $db->query("DESCRIBE `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($columns as $col) {
            $field = $col['Field'];
            $type = $col['Type'];
            $null = $col['Null'];
            $default = $col['Default'];
            $key = $col['Key'];
            
            $hasIssue = false;
            $issueDesc = [];
            
            // Check: Numeric fields with DEFAULT 0 that should be NULL
            if (preg_match('/decimal|int|float|double/i', $type)) {
                if ($default === '0' || $default === '0.00') {
                    // Skip ID fields and foreign keys
                    if ($key !== 'PRI' && !preg_match('/_id$/i', $field)) {
                        $hasIssue = true;
                        $issueDesc[] = "⚠️ DEFAULT 0 ควรเป็น NULL";
                    }
                }
            }
            
            // Check: String fields that don't allow NULL but should
            if (preg_match('/varchar|text|char/i', $type)) {
                if ($null === 'NO' && $key !== 'PRI') {
                    // Skip required fields like name, email, code
                    if (!preg_match('/^(email|password|name|code|type_code|category_code)$/i', $field)) {
                        $hasIssue = true;
                        $issueDesc[] = "⚠️ NOT NULL แต่อาจต้องการ NULL";
                    }
                }
            }
            
            // Check: Fields with empty string default
            if ($default === '') {
                $hasIssue = true;
                $issueDesc[] = "⚠️ DEFAULT '' ควรเป็น NULL";
            }
            
            // Display column info
            $nullStatus = $null === 'YES' ? '✅ NULL' : '❌ NOT NULL';
            $defaultStr = $default === null ? 'NULL' : "'{$default}'";
            
            if ($hasIssue) {
                echo "  🔴 {$field}\n";
                echo "     Type: {$type} | {$nullStatus} | Default: {$defaultStr}\n";
                foreach ($issueDesc as $issue) {
                    echo "     {$issue}\n";
                }
                $issues[] = [
                    'table' => $table,
                    'field' => $field,
                    'type' => $type,
                    'null' => $null,
                    'default' => $default,
                    'issues' => $issueDesc
                ];
            } else {
                echo "  🟢 {$field} | {$type} | {$nullStatus} | Default: {$defaultStr}\n";
            }
        }
        echo "\n";
    }
    
    // Summary
    echo "\n==============================================\n";
    echo "  📊 สรุปผลการตรวจสอบ\n";
    echo "==============================================\n\n";
    
    if (count($issues) > 0) {
        echo "พบ " . count($issues) . " field ที่ต้องแก้ไข:\n\n";
        
        // Group by table
        $grouped = [];
        foreach ($issues as $issue) {
            $grouped[$issue['table']][] = $issue;
        }
        
        foreach ($grouped as $table => $tableIssues) {
            echo "📁 {$table}:\n";
            foreach ($tableIssues as $issue) {
                echo "   - {$issue['field']}: " . implode(', ', $issue['issues']) . "\n";
            }
            echo "\n";
        }
        
        // Generate ALTER statements
        echo "\n==============================================\n";
        echo "  🔧 SQL สำหรับแก้ไข\n";
        echo "==============================================\n\n";
        
        foreach ($grouped as $table => $tableIssues) {
            echo "-- Table: {$table}\n";
            foreach ($tableIssues as $issue) {
                $type = $issue['type'];
                $field = $issue['field'];
                
                // Generate ALTER statement to allow NULL and remove default
                echo "ALTER TABLE `{$table}` MODIFY COLUMN `{$field}` {$type} NULL DEFAULT NULL;\n";
            }
            echo "\n";
        }
        
    } else {
        echo "✅ ไม่พบปัญหาใดๆ ทุก field ถูกต้อง\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
