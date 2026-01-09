<?php
/**
 * Import Script for budget_structure2schema.csv
 * 
 * This script imports the CSV file located at docs/budget_structure2schema.csv
 * into the database tables following the complete budget structure schema.
 * 
 * Usage: php scripts/import_budget_structure_v2.php
 * 
 * @version 2.0
 * @date 2026-01-01
 */

ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
set_time_limit(0);
error_reporting(E_ALL);

echo "==============================================\n";
echo " Budget Structure CSV Importer v2.0\n";
echo " Date: " . date('Y-m-d H:i:s') . "\n";
echo "==============================================\n\n";

// ============================================
// Configuration
// ============================================
$config = [
    'csv_file' => __DIR__ . '/../docs/budget_structure2schema.csv',
    'fiscal_year' => 2569,
    'cleanup_before_import' => true,  // Cleanup FY-specific data only
    'full_cleanup_mode' => true,      // ⚠️ FULL WIPE: Delete ALL master data before import
    'db' => [
        'host' => 'localhost',
        'name' => 'hr_budget',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4'
    ]
];

// ============================================
// Database Connection
// ============================================
try {
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset={$config['db']['charset']}";
    $pdo = new PDO($dsn, $config['db']['user'], $config['db']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec("SET NAMES utf8mb4");
    echo "[OK] Database connected\n";
} catch (PDOException $e) {
    die("[ERROR] Database connection failed: " . $e->getMessage() . "\n");
}

// ============================================
// Cache for Lookup/Insert operations
// ============================================
$cache = [
    'budget_types' => [],
    'plans' => [],
    'projects' => [],
    'activities' => [],
    'expense_types' => [],
    'expense_groups' => [],
    'expense_items' => [],
    'organizations' => [],
    'provinces' => [],
    'province_groups' => [],
    'province_zones' => [],
    'inspection_zones' => [],
];

// ============================================
// Helper Functions
// ============================================

/**
 * Fix double-encoded Thai text (TIS-620 stored as UTF-8)
 */
function fixEncoding($str) {
    $str = trim($str);
    if (empty($str)) return $str;
    
    // Check if already proper UTF-8 Thai
    if (preg_match('/[\x{0E00}-\x{0E7F}]/u', $str)) {
        return $str; // Already valid Thai UTF-8
    }
    
    // Try to fix double encoding: UTF-8 chars -> bytes -> TIS-620 -> UTF-8
    $bytes = @utf8_decode($str);
    $fixed = @iconv('TIS-620', 'UTF-8//IGNORE', $bytes);
    
    return $fixed ?: $str;
}

/**
 * Get existing record ID or insert new record
 */
function getOrInsert($pdo, &$cache, $table, $criteria, $data) {
    // Build cache key from criteria values
    $cacheKey = implode('|', array_map(function($v) { return $v ?? 'NULL'; }, array_values($criteria)));
    
    // Check cache first
    if (isset($cache[$table][$cacheKey])) {
        return $cache[$table][$cacheKey];
    }
    
    // Build WHERE clause
    $where = [];
    $params = [];
    foreach ($criteria as $col => $val) {
        if ($val === null) {
            $where[] = "`$col` IS NULL";
        } else {
            $where[] = "`$col` = ?";
            $params[] = $val;
        }
    }
    
    // Try to find existing record
    $sql = "SELECT id FROM `$table` WHERE " . implode(" AND ", $where) . " LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $id = $stmt->fetchColumn();
    
    if ($id) {
        $cache[$table][$cacheKey] = $id;
        return $id;
    }
    
    // Insert new record
    $cols = array_keys($data);
    $placeholders = str_repeat('?,', count($cols) - 1) . '?';
    $sql = "INSERT INTO `$table` (`" . implode('`,`', $cols) . "`) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    $id = $pdo->lastInsertId();
    
    $cache[$table][$cacheKey] = $id;
    return $id;
}

/**
 * Pre-load existing data into cache
 */
function preloadCache($pdo, &$cache) {
    echo "[INFO] Pre-loading cache...\n";
    
    // Budget Types
    $stmt = $pdo->query("SELECT id, name_th FROM budget_types");
    while ($r = $stmt->fetch()) {
        $cache['budget_types'][$r['name_th']] = $r['id'];
    }
    echo "  - budget_types: " . count($cache['budget_types']) . " records\n";
    
    // Expense Types
    $stmt = $pdo->query("SELECT id, name_th FROM expense_types");
    while ($r = $stmt->fetch()) {
        $cache['expense_types'][trim($r['name_th'])] = $r['id'];
    }
    echo "  - expense_types: " . count($cache['expense_types']) . " records\n";
    
    // Provinces
    $stmt = $pdo->query("SELECT id, name_th FROM provinces");
    while ($r = $stmt->fetch()) {
        $cache['provinces'][$r['name_th']] = $r['id'];
    }
    echo "  - provinces: " . count($cache['provinces']) . " records\n";
    
    // Province Groups
    $stmt = $pdo->query("SELECT id, name_th FROM province_groups");
    while ($r = $stmt->fetch()) {
        $cache['province_groups'][$r['name_th']] = $r['id'];
    }
    echo "  - province_groups: " . count($cache['province_groups']) . " records\n";
    
    // Inspection Zones
    $stmt = $pdo->query("SELECT id, name_th FROM inspection_zones");
    while ($r = $stmt->fetch()) {
        $cache['inspection_zones'][$r['name_th']] = $r['id'];
    }
    echo "  - inspection_zones: " . count($cache['inspection_zones']) . " records\n";
}

// ============================================
// Open CSV File
// ============================================
if (!file_exists($config['csv_file'])) {
    die("[ERROR] CSV file not found: {$config['csv_file']}\n");
}

$handle = fopen($config['csv_file'], 'r');
if (!$handle) {
    die("[ERROR] Could not open CSV file\n");
}
echo "[OK] CSV file opened: " . basename($config['csv_file']) . "\n";

// Handle BOM
$bom = fread($handle, 3);
if ($bom !== "\xEF\xBB\xBF") {
    rewind($handle);
}

// Read header
$header = fgetcsv($handle);
echo "[INFO] CSV has " . count($header) . " columns\n";

// Expected columns (for reference):
// 0: ปีงบประมาณ, 1: ประเภทงบประมาณ, 2: แผนงาน, 3: ผลผลิต/โครงการ, 4: กิจกรรม
// 5: ประเภทรายจ่าย, 6: รายการ 0, 7: รายการ 1, 8: รายการ 2, 9: รายการ 3, 10: รายการ 4, 11: รายการ 5
// 12: กระทรวง, 13: กรม, 14: กอง, 15: กลุ่มงาน, 16: จังหวัด, 17: ส่วนราชการ
// 18: กลุ่มจังหวัด, 19: เขตจังหวัด, 20: เขตตรวจราชการ, 21: หมายเหตุ
// 22-29: Admin fields (empty in CSV)

// ============================================
// Cleanup if requested (BEFORE preloading cache)
// ============================================
if ($config['full_cleanup_mode']) {
    echo "\n[WARN] ⚠️  FULL CLEANUP MODE - Deleting ALL data from all tables...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Order matters for clean deletion (child tables first)
    $fullCleanupTables = [
        'budget_line_items',
        'budget_trackings',
        'disbursement_details',
        'disbursement_headers',
        'activities',
        'projects',
        'plans',
        'budget_types',
        'expense_items',
        'expense_groups',
        'expense_types',
        'organizations',
        'provinces',
        'province_zones',
        'province_groups',
        'inspection_zones',
    ];
    
    foreach ($fullCleanupTables as $table) {
        try {
            $deleted = $pdo->exec("DELETE FROM `$table`");
            // Reset auto increment
            $pdo->exec("ALTER TABLE `$table` AUTO_INCREMENT = 1");
            echo "  - Wiped $table (deleted $deleted records, reset AUTO_INCREMENT)\n";
        } catch (PDOException $e) {
            // Table might not exist, skip it
            echo "  - Skipped $table (not found or error)\n";
        }
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "[OK] Full cleanup completed!\n";
    
    // Clear cache since all data was wiped
    foreach ($cache as $key => $val) {
        $cache[$key] = [];
    }
    echo "[INFO] Cache cleared (fresh start).\n";
    
} elseif ($config['cleanup_before_import']) {
    echo "\n[WARN] Cleaning up FY {$config['fiscal_year']} data only...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    $tables = ['budget_line_items', 'activities', 'projects', 'plans'];
    foreach ($tables as $table) {
        $deleted = $pdo->exec("DELETE FROM `$table` WHERE fiscal_year = {$config['fiscal_year']}");
        echo "  - Deleted $deleted records from $table\n";
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    // Preload cache for non-full cleanup (to reuse existing master data)
    preloadCache($pdo, $cache);
}

// ============================================
// Main Import Loop
// ============================================
echo "\n[INFO] Starting import...\n";
$pdo->beginTransaction();

$stats = [
    'rows_processed' => 0,
    'rows_skipped' => 0,
    'budget_types_created' => 0,
    'plans_created' => 0,
    'projects_created' => 0,
    'activities_created' => 0,
    'expense_items_created' => 0,
    'line_items_created' => 0,
];

try {
    while (($row = fgetcsv($handle)) !== false) {
        $stats['rows_processed']++;
        
        // Skip empty rows
        if (empty(array_filter($row))) {
            $stats['rows_skipped']++;
            continue;
        }
        
        // Progress indicator
        if ($stats['rows_processed'] % 20 == 0) {
            echo "  Processing row {$stats['rows_processed']}...\n";
        }
        
        // ========================================
        // Parse CSV Row
        // ========================================
        $fiscalYear = trim($row[0]) ?: $config['fiscal_year'];
        $budgetTypeName = fixEncoding(trim($row[1] ?? ''));
        $planName = fixEncoding(trim($row[2] ?? ''));
        $projectName = fixEncoding(trim($row[3] ?? ''));
        $activityName = fixEncoding(trim($row[4] ?? ''));
        $expenseTypeName = fixEncoding(trim($row[5] ?? ''));
        
        // Item levels 0-5 (columns 6-11)
        $items = [];
        for ($i = 0; $i <= 5; $i++) {
            $items[$i] = fixEncoding(trim($row[6 + $i] ?? ''));
        }
        
        // Organization (columns 12-15)
        $ministryName = fixEncoding(trim($row[12] ?? ''));
        $deptName = fixEncoding(trim($row[13] ?? ''));
        $divName = fixEncoding(trim($row[14] ?? ''));
        $sectName = fixEncoding(trim($row[15] ?? ''));
        
        // Geography (columns 16-20)
        $provinceName = fixEncoding(trim($row[16] ?? ''));
        $regionType = fixEncoding(trim($row[17] ?? ''));  // ส่วนกลาง, etc.
        $provGroupName = fixEncoding(trim($row[18] ?? ''));
        $provZoneName = fixEncoding(trim($row[19] ?? ''));
        $inspZoneName = fixEncoding(trim($row[20] ?? ''));
        $remark = fixEncoding(trim($row[21] ?? ''));
        
        // Skip if no budget type
        if (empty($budgetTypeName)) {
            $stats['rows_skipped']++;
            continue;
        }
        
        // ========================================
        // 1. Budget Type
        // ========================================
        $budgetTypeId = getOrInsert($pdo, $cache, 'budget_types',
            ['name_th' => $budgetTypeName],
            ['name_th' => $budgetTypeName, 'code' => 'BT-' . substr(md5($budgetTypeName), 0, 6)]
        );
        
        // ========================================
        // 2. Plan
        // ========================================
        $planId = null;
        if ($planName) {
            $planId = getOrInsert($pdo, $cache, 'plans',
                ['name_th' => $planName, 'budget_type_id' => $budgetTypeId, 'fiscal_year' => $fiscalYear],
                ['name_th' => $planName, 'budget_type_id' => $budgetTypeId, 'fiscal_year' => $fiscalYear, 'code' => 'PL-' . substr(md5($planName), 0, 6)]
            );
        }
        
        // ========================================
        // 3. Project
        // ========================================
        $projectId = null;
        if ($projectName && $planId) {
            $projectId = getOrInsert($pdo, $cache, 'projects',
                ['name_th' => $projectName, 'plan_id' => $planId, 'fiscal_year' => $fiscalYear],
                ['name_th' => $projectName, 'plan_id' => $planId, 'fiscal_year' => $fiscalYear, 'code' => 'PJ-' . substr(md5($projectName), 0, 6)]
            );
        }
        
        // ========================================
        // 4. Activity
        // ========================================
        $activityId = null;
        if ($activityName) {
            $actCriteria = ['name_th' => $activityName, 'fiscal_year' => $fiscalYear];
            if ($projectId) $actCriteria['project_id'] = $projectId;
            elseif ($planId) $actCriteria['plan_id'] = $planId;
            
            $actData = $actCriteria;
            $actData['code'] = 'AC-' . substr(md5($activityName), 0, 6);
            
            $activityId = getOrInsert($pdo, $cache, 'activities', $actCriteria, $actData);
        }
        
        // ========================================
        // 5. Expense Type
        // ========================================
        $expenseTypeId = null;
        if ($expenseTypeName) {
            $expenseTypeName = trim($expenseTypeName); // Remove trailing space
            $expenseTypeId = getOrInsert($pdo, $cache, 'expense_types',
                ['name_th' => $expenseTypeName],
                ['name_th' => $expenseTypeName, 'code' => 'ET-' . substr(md5($expenseTypeName), 0, 4)]
            );
        }
        
        // ========================================
        // 6. Expense Group (รายการ 0)
        // ========================================
        $expenseGroupId = null;
        if ($items[0] && $expenseTypeId) {
            $expenseGroupId = getOrInsert($pdo, $cache, 'expense_groups',
                ['name_th' => $items[0], 'expense_type_id' => $expenseTypeId],
                ['name_th' => $items[0], 'expense_type_id' => $expenseTypeId, 'code' => 'EG-' . substr(md5($items[0]), 0, 4)]
            );
        }
        
        // ========================================
        // 7. Expense Items (รายการ 1-5, hierarchical)
        // ========================================
        $expenseItemParentId = null;
        $finalExpenseItemId = null;
        
        for ($level = 1; $level <= 5; $level++) {
            $itemName = $items[$level];
            if (empty($itemName) || $itemName === 'รายการย่อย ...') break;
            
            $criteria = [
                'name_th' => $itemName,
                'expense_group_id' => $expenseGroupId,
                'parent_id' => $expenseItemParentId
            ];
            
            $data = $criteria;
            $data['level'] = $level;
            $data['code'] = 'EI-' . substr(md5($itemName . $level), 0, 4);
            
            $itemId = getOrInsert($pdo, $cache, 'expense_items', $criteria, $data);
            $expenseItemParentId = $itemId;
            $finalExpenseItemId = $itemId;
        }
        
        // ========================================
        // 8. Organization Hierarchy
        // ========================================
        $ministryId = $deptId = $divId = $sectId = null;
        $orgParentId = null;
        
        // Ministry (Level 0)
        if ($ministryName) {
            $ministryId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $ministryName, 'level' => 0],
                ['name_th' => $ministryName, 'level' => 0, 'org_type' => 'ministry', 'code' => 'MN-' . substr(md5($ministryName), 0, 4)]
            );
            $orgParentId = $ministryId;
        }
        
        // Department (Level 1)
        if ($deptName && $orgParentId) {
            $deptId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $deptName, 'parent_id' => $orgParentId],
                ['name_th' => $deptName, 'parent_id' => $orgParentId, 'level' => 1, 'org_type' => 'department', 'code' => 'DP-' . substr(md5($deptName), 0, 4)]
            );
            $orgParentId = $deptId;
        }
        
        // Division (Level 2)
        if ($divName && $orgParentId) {
            $divId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $divName, 'parent_id' => $orgParentId],
                ['name_th' => $divName, 'parent_id' => $orgParentId, 'level' => 2, 'org_type' => 'division', 'code' => 'DV-' . substr(md5($divName), 0, 4)]
            );
            $orgParentId = $divId;
        }
        
        // Section (Level 3)
        if ($sectName && $orgParentId) {
            $regionMap = [
                'ส่วนกลาง' => 'central',
                'ส่วนภูมิภาค' => 'regional',
                'จังหวัด' => 'provincial',
                'ส่วนกลางที่ตั้งอยู่ในภูมิภาค' => 'central'
            ];
            $region = $regionMap[$regionType] ?? 'central';
            
            $sectId = getOrInsert($pdo, $cache, 'organizations',
                ['name_th' => $sectName, 'parent_id' => $orgParentId],
                ['name_th' => $sectName, 'parent_id' => $orgParentId, 'level' => 3, 'org_type' => 'section', 'region' => $region, 'code' => 'SC-' . substr(md5($sectName), 0, 4)]
            );
        }
        
        // ========================================
        // 9. Geography
        // ========================================
        $provinceId = $provGroupId = $provZoneId = $inspZoneId = null;
        
        if ($provinceName) {
            $provinceId = getOrInsert($pdo, $cache, 'provinces',
                ['name_th' => $provinceName],
                ['name_th' => $provinceName, 'code' => 'PR-' . substr(md5($provinceName), 0, 4)]
            );
        }
        
        if ($provGroupName) {
            $provGroupId = getOrInsert($pdo, $cache, 'province_groups',
                ['name_th' => $provGroupName],
                ['name_th' => $provGroupName, 'code' => 'PG-' . substr(md5($provGroupName), 0, 4)]
            );
        }
        
        if ($provZoneName) {
            $provZoneId = getOrInsert($pdo, $cache, 'province_zones',
                ['name_th' => $provZoneName],
                ['name_th' => $provZoneName, 'code' => 'PZ-' . substr(md5($provZoneName), 0, 4), 'province_group_id' => $provGroupId]
            );
        }
        
        if ($inspZoneName) {
            $inspZoneId = getOrInsert($pdo, $cache, 'inspection_zones',
                ['name_th' => $inspZoneName],
                ['name_th' => $inspZoneName, 'code' => 'IZ-' . substr(md5($inspZoneName), 0, 4)]
            );
        }
        
        // ========================================
        // 10. Insert Budget Line Item
        // ========================================
        $lineRegion = ($regionType === 'ส่วนภูมิภาค' || $regionType === 'จังหวัด') ? 'regional' : 'central';
        
        $sql = "INSERT INTO budget_line_items (
            fiscal_year, budget_type_id, plan_id, project_id, activity_id,
            expense_type_id, expense_group_id, expense_item_id,
            ministry_id, department_id, division_id, section_id,
            province_id, province_group_id, province_zone_id, inspection_zone_id,
            region_type, remarks
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $fiscalYear, $budgetTypeId, $planId, $projectId, $activityId,
            $expenseTypeId, $expenseGroupId, $finalExpenseItemId,
            $ministryId, $deptId, $divId, $sectId,
            $provinceId, $provGroupId, $provZoneId, $inspZoneId,
            $lineRegion, $remark
        ]);
        
        $stats['line_items_created']++;
    }
    
    $pdo->commit();
    echo "\n[OK] Import completed successfully!\n";
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo "\n[ERROR] Import failed: " . $e->getMessage() . "\n";
    echo "[TRACE] " . $e->getTraceAsString() . "\n";
    exit(1);
}

fclose($handle);

// ============================================
// Summary
// ============================================
echo "\n==============================================\n";
echo " Import Summary\n";
echo "==============================================\n";
echo " Rows Processed: {$stats['rows_processed']}\n";
echo " Rows Skipped:   {$stats['rows_skipped']}\n";
echo " Line Items:     {$stats['line_items_created']}\n";
echo "==============================================\n";

// Show table counts
echo "\n[INFO] Current table counts:\n";
$tables = ['budget_types', 'plans', 'projects', 'activities', 'expense_types', 'expense_groups', 'expense_items', 'organizations', 'budget_line_items'];
foreach ($tables as $table) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    echo "  - $table: $count records\n";
}

echo "\n[DONE] Script finished at " . date('Y-m-d H:i:s') . "\n";
