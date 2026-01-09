<?php
/**
 * Run New Schema Migration
 * สำหรับ run migration 010_new_schema_dimensional.sql
 * 
 * เพิ่มตาราง Dimensional Model และลบ DEFAULT 0 จาก numeric fields
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

echo "==============================================\n";
echo "  NEW SCHEMA MIGRATION\n";
echo "  Dimensional Model + Remove DEFAULT 0\n";
echo "==============================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Read migration file
    $sqlFile = __DIR__ . '/../database/migrations/010_new_schema_dimensional.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Migration file not found: {$sqlFile}");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Split by statement (handle multi-line statements)
    // Remove comments first
    $sql = preg_replace('/--.*$/m', '', $sql);
    
    // Split by semicolon
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    echo "📋 จำนวน SQL statements: " . count($statements) . "\n\n";
    
    $success = 0;
    $errors = [];
    
    foreach ($statements as $idx => $stmt) {
        if (empty($stmt)) continue;
        
        // Get first line for display
        $firstLine = strtok($stmt, "\n");
        $displayLine = substr($firstLine, 0, 60);
        
        echo sprintf("[%02d] Executing: %s...\n", $idx + 1, $displayLine);
        
        try {
            $db->exec($stmt);
            echo "     ✅ Success\n";
            $success++;
        } catch (PDOException $e) {
            $errorMsg = $e->getMessage();
            
            // Skip "table already exists" errors
            if (strpos($errorMsg, 'already exists') !== false) {
                echo "     ⚠️ Already exists (skipped)\n";
                $success++;
            }
            // Skip "Unknown column" errors for ALTER (column doesn't exist in some tables)
            else if (strpos($errorMsg, 'Unknown column') !== false) {
                echo "     ⚠️ Column not found (skipped)\n";
            }
            // Skip table doesn't exist errors
            else if (strpos($errorMsg, "doesn't exist") !== false) {
                echo "     ⚠️ Table doesn't exist (skipped)\n";
            }
            else {
                echo "     ❌ Error: " . $errorMsg . "\n";
                $errors[] = [
                    'statement' => $displayLine,
                    'error' => $errorMsg
                ];
            }
        }
    }
    
    echo "\n==============================================\n";
    echo "  📊 สรุปผล\n";
    echo "==============================================\n\n";
    
    echo "✅ Success: {$success}\n";
    echo "❌ Errors: " . count($errors) . "\n";
    
    if (count($errors) > 0) {
        echo "\n⚠️ รายการที่มีปัญหา:\n";
        foreach ($errors as $err) {
            echo "  - {$err['statement']}\n";
            echo "    Error: {$err['error']}\n";
        }
    }
    
    // Verify new tables
    echo "\n==============================================\n";
    echo "  🔍 ตรวจสอบตารางใหม่\n";
    echo "==============================================\n\n";
    
    $newTables = ['dim_organization', 'dim_budget_structure', 'fact_budget_execution', 'log_transfer_note'];
    
    foreach ($newTables as $table) {
        try {
            $result = $db->query("SELECT COUNT(*) as cnt FROM `{$table}`")->fetch(PDO::FETCH_ASSOC);
            echo "✅ {$table}: exists (rows: {$result['cnt']})\n";
        } catch (PDOException $e) {
            echo "❌ {$table}: NOT exists\n";
        }
    }
    
    echo "\n✅ Migration completed!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
