<?php
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';

use Illuminate\Support\Collection;

// DB Connection
$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Starting Budget CSV Import...\n";

// Load Caches
$cache = [
    'budget_types' => [],
    'plans' => [],
    'projects' => [],
    'activities' => [],
    'expense_types' => [],
    'expense_groups' => [],
    'expense_items' => [], // Key: group_id|parent_id|name
    'organizations' => [], // Key: parent_id|name
    'provinces' => [],
    'province_groups' => [],
    'province_zones' => [],
    'inspection_zones' => [],
];

// Pre-load common lookups
$stmt = $pdo->query("SELECT id, name_th FROM budget_types");
while ($r = $stmt->fetch()) $cache['budget_types'][$r['name_th']] = $r['id'];

$stmt = $pdo->query("SELECT id, name_th FROM expense_types");
while ($r = $stmt->fetch()) $cache['expense_types'][$r['name_th']] = $r['id'];

$stmt = $pdo->query("SELECT id, name_th FROM expense_groups");
while ($r = $stmt->fetch()) {
    // Note: Names might duplicate across types, strictly we should use type_id
    // But name is usually unique enough for seeding logic
    $cache['expense_groups'][$r['name_th']] = $r['id'];
}

$stmt = $pdo->query("SELECT id, name_th FROM provinces");
while ($r = $stmt->fetch()) $cache['provinces'][$r['name_th']] = $r['id'];

$stmt = $pdo->query("SELECT id, name_th, code FROM province_groups");
while ($r = $stmt->fetch()) $cache['province_groups'][$r['name_th']] = $r['id'];

$stmt = $pdo->query("SELECT id, name_th, code FROM inspection_zones");
while ($r = $stmt->fetch()) $cache['inspection_zones'][$r['name_th']] = $r['id'];

// Helper Functions
function getOrInsert($pdo, &$cache, $table, $criteria, $data) {
    // Criteria is array of col=>val meant for uniqueness check
    // Data is array of col=>val to insert
    
    // Generate cache key
    $key = implode('|', array_values($criteria));
    if (isset($cache[$table][$key])) {
        return $cache[$table][$key];
    }

    // Lookup DB
    $where = [];
    $params = [];
    foreach ($criteria as $col => $val) {
        if ($val === null) $where[] = "$col IS NULL";
        else {
            $where[] = "$col = ?";
            $params[] = $val;
        }
    }
    $sql = "SELECT id FROM $table WHERE " . implode(" AND ", $where) . " LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $id = $stmt->fetchColumn();

    if ($id) {
        $cache[$table][$key] = $id;
        return $id;
    }

    // Insert
    $cols = array_keys($data);
    $vals = array_values($data);
    $placeholders = str_repeat('?,', count($cols) - 1) . '?';
    $sql = "INSERT INTO $table (" . implode(',', $cols) . ") VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($vals);
    $id = $pdo->lastInsertId();
    
    $cache[$table][$key] = $id;
    return $id;
}

// Open CSV - Try Research file (likely cleaner)
$csvFile = __DIR__ . '/../research/budget_structure_2569.csv';
$handle = fopen($csvFile, 'r');
if (!$handle) {
    // Fallback
    $csvFile = __DIR__ . '/../docs/budget_structure2schema.csv';
    $handle = fopen($csvFile, 'r');
    if (!$handle) die("Could not open CSV (tried research and docs)");
}
echo "Importing from: " . basename($csvFile) . "\n";
// Update Version ID
echo "Script Version: IMPORT_V4_RESEARCH_CSV\n";

// Skip header
// Handle BOM
$bom = fread($handle, 3);
if ($bom != "\xEF\xBB\xBF") rewind($handle);
fgetcsv($handle); // Header

// CLEANUP: Clean 2569 data to prevent duplicates from bad runs
echo "Cleaning up FY 2569 data...\n";
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
$pdo->exec("DELETE FROM budget_line_items WHERE fiscal_year = 2569");
$pdo->exec("DELETE FROM activities WHERE fiscal_year = 2569");
$pdo->exec("DELETE FROM projects WHERE fiscal_year = 2569");
$pdo->exec("DELETE FROM plans WHERE fiscal_year = 2569");
// For organizations, it's riskier to mass delete. 
// We'll rely on getOrInsert finding the correct name if it exists, or inserting new.
// If previous names were corrupt, they won't be matched, so new correct ones will be made.
// We can manually cleanup organizations later if needed.
$pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

$rowCount = 0;
// FORCE UTF-8
$pdo->exec("SET NAMES utf8mb4");
$pdo->beginTransaction();

// Helper: CSV contains DOUBLE ENCODED text
// The file has TIS-620 bytes that were saved as UTF-8 characters
// To fix: UTF-8 chars -> bytes -> TIS-620 -> proper UTF-8
function fix_encoding($str) {
    $str = trim($str);
    if (empty($str)) return $str;
    
    // Step 1: Decode UTF-8 to get raw bytes (as ISO-8859-1)
    $bytes = utf8_decode($str);
    
    // Step 2: Interpret those bytes as TIS-620 and convert to proper UTF-8
    $fixed = @iconv('TIS-620', 'UTF-8//IGNORE', $bytes);
    
    // Return fixed version, or original if conversion failed
    return $fixed ?: $str;
}
// Log version
echo "Script Version: IMPORT_V5_DOUBLE_ENCODING_FIX\n";

// ... existing code ...

    try {
        while (($row = fgetcsv($handle)) !== false) {
            $rowCount++;
            if ($rowCount % 100 == 0) echo "Processing row $rowCount...\n";

            // Map Columns & Clean Encoding
            $fiscalYear = trim($row[0]);
            $budgetTypeName = fix_encoding(trim($row[1]));
            $planName = fix_encoding(trim($row[2]));
            $projectName = fix_encoding(trim($row[3]));
            $activityName = fix_encoding(trim($row[4]));
            $expenseTypeName = fix_encoding(trim($row[5]));
            $expenseGroupName = fix_encoding(trim($row[6]));
        // Items 7-11
        $ministryName = fix_encoding(trim($row[12]));
        $deptName = fix_encoding(trim($row[13]));
        $divName = fix_encoding(trim($row[14]));
        $sectName = fix_encoding(trim($row[15]));
        $provinceName = fix_encoding(trim($row[16]));
        $regionType = fix_encoding(trim($row[17]));
        $provGroupName = fix_encoding(trim($row[18]));
        $provZoneName = fix_encoding(trim($row[19]));
        $inspZoneName = trim($row[20]);
        // $remark = trim($row[21]);

        // 1. Budget Type
        if (empty($budgetTypeName)) continue;
        // Simple lookup (assume exists or create basic)
        // If not matches 'BUK', 'UNIT', 'INTEG' -> create generic?
        // Let's assume seeded types cover it, or insert name as code.
        $budgetTypeId = getOrInsert($pdo, $cache, 'budget_types', ['name_th' => $budgetTypeName], ['name_th' => $budgetTypeName, 'code' => substr(md5($budgetTypeName), 0, 10)]);

        // 2. Plan
        $planId = null;
        if ($planName) {
            $planId = getOrInsert($pdo, $cache, 'plans', 
                ['name_th' => $planName, 'budget_type_id' => $budgetTypeId],
                ['name_th' => $planName, 'budget_type_id' => $budgetTypeId, 'fiscal_year' => $fiscalYear, 'code' => 'P-'.uniqid()]
            );
        }

        // 3. Project
        $projectId = null;
        if ($projectName && $planId) {
            $projectId = getOrInsert($pdo, $cache, 'projects',
                ['name_th' => $projectName, 'plan_id' => $planId],
                ['name_th' => $projectName, 'plan_id' => $planId, 'fiscal_year' => $fiscalYear, 'code' => 'PJ-'.uniqid()]
            );
        }

        // 4. Activity (New)
        $activityId = null;
        if ($activityName) {
            $activityCriteria = ['name_th' => $activityName];
            if ($projectId) $activityCriteria['project_id'] = $projectId;
            else if ($planId) $activityCriteria['plan_id'] = $planId;

            $activityData = $activityCriteria;
            $activityData['fiscal_year'] = $fiscalYear;
            $activityData['code'] = 'ACT-'.uniqid();
            
            $activityId = getOrInsert($pdo, $cache, 'activities', $activityCriteria, $activityData);
        }

        // 5. Expense Type
        $expenseTypeId = null;
        if ($expenseTypeName) {
            // Trim spaces
            $expenseTypeName = trim($expenseTypeName);
            $expenseTypeId = getOrInsert($pdo, $cache, 'expense_types', ['name_th' => $expenseTypeName], ['name_th' => $expenseTypeName, 'code' => substr(md5($expenseTypeName), 0, 5)]);
        }

        // 6. Expense Group
        $expenseGroupId = null;
        if ($expenseGroupName && $expenseTypeId) {
            $expenseGroupId = getOrInsert($pdo, $cache, 'expense_groups',
                ['name_th' => $expenseGroupName, 'expense_type_id' => $expenseTypeId],
                ['name_th' => $expenseGroupName, 'expense_type_id' => $expenseTypeId, 'code' => 'EG-'.uniqid()]
            );
        }

        // 7. Expense Hierarchy (Items 0-5 => Cols 7-11)
        // Col 7 is Level 1, Col 8 is Level 2...
        $parentId = null;
        $finalExpenseItemId = null;
        
        $itemCols = range(7, 11);
        foreach ($itemCols as $i => $colIndex) {
            $itemName = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
            if (empty($itemName)) break; // Stop at first empty level

            $level = $i - 6; // Col 7 = Level 1
            // Use level = $i - 6. Wait, migration said "Level 0-5". 
            // If expense_group is level 0 concept, then this is level 1+.
            
            $criteria = [
                'name_th' => $itemName,
                'expense_group_id' => $expenseGroupId,
                'parent_id' => $parentId
            ];
            
            $data = $criteria;
            $data['level'] = $level;
            
            // To lookup correctly in getOrInsert, we need accurate parent_id match
             // Note: getOrInsert generic function uses cache key from criteria
            
            $itemId = getOrInsert($pdo, $cache, 'expense_items', $criteria, $data);
            $parentId = $itemId;
            $finalExpenseItemId = $itemId;
        }

        // 8. Organization Hierarchy
        $orgParentId = null;
        $finalOrgId = null;
        
        // Ministry
        if ($ministryName) {
            $ministryId = getOrInsert($pdo, $cache, 'organizations', ['name_th' => $ministryName, 'level' => 1], ['name_th' => $ministryName, 'level' => 1, 'org_type' => 'ministry', 'code' => 'MIN-'.uniqid()]);
            $orgParentId = $ministryId;
            $finalOrgId = $ministryId;
        }
        
        // Department
        if ($deptName && $orgParentId) {
            $deptId = getOrInsert($pdo, $cache, 'organizations', ['name_th' => $deptName, 'parent_id' => $orgParentId], ['name_th' => $deptName, 'parent_id' => $orgParentId, 'level' => 2, 'org_type' => 'department', 'code' => 'DPT-'.uniqid()]);
            $orgParentId = $deptId;
            $finalOrgId = $deptId;
        }

        // Division
        if ($divName && $orgParentId) {
            $divId = getOrInsert($pdo, $cache, 'organizations', ['name_th' => $divName, 'parent_id' => $orgParentId], ['name_th' => $divName, 'parent_id' => $orgParentId, 'level' => 3, 'org_type' => 'division', 'code' => 'DIV-'.uniqid()]);
            $orgParentId = $divId;
            $finalOrgId = $divId;
        }

        // Section
        if ($sectName && $orgParentId) {
            $sectData = ['name_th' => $sectName, 'parent_id' => $orgParentId, 'level' => 4, 'org_type' => 'section', 'code' => 'SEC-'.uniqid()];
            // Handle Region Type for Section
            $regionMap = [
                'ส่วนกลาง' => 'central',
                'ส่วนภูมิภาค' => 'regional',
                'จังหวัด' => 'provincial',
                'ส่วนกลางที่ตั้งอยู่ในภูมิภาค' => 'central_in_region'
            ];
            if ($regionType && isset($regionMap[$regionType])) {
                $sectData['region'] = $regionMap[$regionType];
            }
            
            $sectId = getOrInsert($pdo, $cache, 'organizations', ['name_th' => $sectName, 'parent_id' => $orgParentId], $sectData);
            $orgParentId = $sectId;
            $finalOrgId = $sectId;
        } else if ($finalOrgId && $regionType) {
            // If stopped at Department or Division, maybe update its region? 
            // Better not override existing org region from CSV row unless strictly needed.
            // But if it's new insert, we set it.
        }

        // 9. Province & Zones
        $provinceId = null;
        if ($provinceName) {
             $provinceId = getOrInsert($pdo, $cache, 'provinces', ['name_th' => $provinceName], ['name_th' => $provinceName, 'code' => substr(md5($provinceName),0,5)]);
        }

        $provGroupId = null;
        if ($provGroupName) {
            $provGroupId = getOrInsert($pdo, $cache, 'province_groups', ['name_th' => $provGroupName], ['name_th' => $provGroupName, 'code' => 'PG-'.uniqid()]);
        }

        $provZoneId = null;
        if ($provZoneName) {
            $provZoneId = getOrInsert($pdo, $cache, 'province_zones', ['name_th' => $provZoneName], ['name_th' => $provZoneName, 'code' => 'PZ-'.uniqid(), 'province_group_id' => $provGroupId]);
        }
        
        $inspZoneId = null;
        if ($inspZoneName) {
            $inspZoneId = getOrInsert($pdo, $cache, 'inspection_zones', ['name_th' => $inspZoneName], ['name_th' => $inspZoneName, 'code' => 'IZ-'.uniqid()]);
        }
        
        // 10. Update Province Relations if needed
        if ($provinceId) {
            // Optional: Update province with group/zone info if missing
            // $pdo->exec("UPDATE provinces SET province_group_id = $provGroupId WHERE id = $provinceId AND province_group_id IS NULL");
        }

        // 11. Insert Budget Line Item
        // Map region_type again for line item
        $lineRegion = 'central'; 
        if ($regionType) {
             $map = ['ส่วนกลาง'=>'central', 'ส่วนภูมิภาค'=>'regional', 'จังหวัด'=>'provincial']; // etc
             if (isset($map[$regionType])) $lineRegion = $map[$regionType];
             if ($regionType == 'ส่วนกลางที่ตั้งอยู่ในภูมิภาค') $lineRegion = 'central'; // Assuming schema uses ENUM('central','regional') for budget_line_items? 
             // Oh `budget_line_items` schema: region_type ENUM('central', 'regional').
             // 'central_in_region' is for Organizations table.
             // Usually central_in_region counts as 'central' or 'regional' allocation?
             // Let's default to 'central' for now, or 'regional' if explicitly provincial.
             if ($regionType == 'ส่วนภูมิภาค' || $regionType == 'จังหวัด') $lineRegion = 'regional';
        }

        $sql = "INSERT INTO budget_line_items (
            fiscal_year, budget_type_id, plan_id, project_id, activity_id,
            expense_type_id, expense_group_id, expense_item_id,
            ministry_id, department_id, division_id, section_id,
            province_id, province_group_id, province_zone_id, inspection_zone_id,
            region_type
        ) VALUES (
            ?, ?, ?, ?, ?,
            ?, ?, ?,
            ?, ?, ?, ?,
            ?, ?, ?, ?,
            ?
        )";
        
        // Need to extract org IDs for hierarchy columns
        // finalOrgId points to the lowest. We need to ascend or just store what we have.
        // Actually we traversed: ministry, dept, div, section.
        // We can just use the vars $ministryId, $deptId, $divId, $sectId (check if set)

        $params = [
            $fiscalYear, $budgetTypeId, $planId, $projectId, $activityId,
            $expenseTypeId, $expenseGroupId, $finalExpenseItemId,
            isset($ministryId) ? $ministryId : null,
            isset($deptId) ? $deptId : null,
            isset($divId) ? $divId : null,
            isset($sectId) ? $sectId : null,
            $provinceId, $provGroupId, $provZoneId, $inspZoneId,
            $lineRegion
        ];
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

    }
    
    $pdo->commit();
    echo "Import Completed successfully! Rows processed: $rowCount\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
