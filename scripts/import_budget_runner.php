<?php
set_time_limit(0);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Collection;

// DB Connection
$dsn = "mysql:host=localhost;dbname=hr_budget;charset=utf8mb4";
$pdo = new PDO($dsn, "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<h1>Starting Budget CSV Import...</h1>";
if (ob_get_level() > 0) ob_end_flush();
flush();

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

// Open CSV
$csvFile = __DIR__ . '/research/budget_structure_2569.csv';
if (!file_exists($csvFile)) die("CSV not found at $csvFile");

$handle = fopen($csvFile, 'r');
if (!$handle) die("Could not open CSV");

// Skip header
$bom = fread($handle, 3);
if ($bom != "\xEF\xBB\xBF") rewind($handle);
fgetcsv($handle); // Header

$rowCount = 0;
$pdo->beginTransaction();

try {
    while (($row = fgetcsv($handle)) !== false) {
        $rowCount++;
        if ($rowCount % 500 == 0) { // Larger chunk for output
            echo "<p>Processing row $rowCount...</p>";
            if (ob_get_level() > 0) ob_flush();
            flush();
        }

        // Map Columns
        $fiscalYear = trim($row[0]);
        $budgetTypeName = trim($row[1]);
        $planName = trim($row[2]);
        $projectName = trim($row[3]);
        $activityName = trim($row[4]);
        $expenseTypeName = trim($row[5]);
        $expenseGroupName = trim($row[6]);
        // Items 7-11
        $ministryName = trim($row[12]);
        $deptName = trim($row[13]);
        $divName = trim($row[14]);
        $sectName = trim($row[15]);
        $provinceName = trim($row[16]);
        $regionType = trim($row[17]);
        $provGroupName = trim($row[18]);
        $provZoneName = trim($row[19]);
        $inspZoneName = trim($row[20]);
        
        if (empty($budgetTypeName)) continue;

        // 1. Budget Type
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

        // 4. Activity
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

        // 7. Expense Items
        $parentId = null;
        $finalExpenseItemId = null;
        
        $itemCols = range(7, 11);
        foreach ($itemCols as $i => $colIndex) {
            $itemName = isset($row[$colIndex]) ? trim($row[$colIndex]) : '';
            if (empty($itemName)) break;

            $level = $i - 6; 
            
            $criteria = [
                'name_th' => $itemName,
                'expense_group_id' => $expenseGroupId,
                'parent_id' => $parentId
            ];
            $data = $criteria;
            $data['level'] = $level;
            
            $itemId = getOrInsert($pdo, $cache, 'expense_items', $criteria, $data);
            $parentId = $itemId;
            $finalExpenseItemId = $itemId;
        }

        // 8. Organizations
        $orgParentId = null;
        // Ministry
         if ($ministryName) {
            $ministryId = getOrInsert($pdo, $cache, 'organizations', ['name_th' => $ministryName, 'level' => 1], ['name_th' => $ministryName, 'level' => 1, 'org_type' => 'ministry', 'code' => 'MIN-'.uniqid()]);
            $orgParentId = $ministryId;
        }
        // Dept
        $deptId = null;
        if ($deptName && $orgParentId) {
            $deptId = getOrInsert($pdo, $cache, 'organizations', ['name_th' => $deptName, 'parent_id' => $orgParentId], ['name_th' => $deptName, 'parent_id' => $orgParentId, 'level' => 2, 'org_type' => 'department', 'code' => 'DPT-'.uniqid()]);
            $orgParentId = $deptId;
        }
        // Div
        $divId = null;
        if ($divName && $orgParentId) {
            $divId = getOrInsert($pdo, $cache, 'organizations', ['name_th' => $divName, 'parent_id' => $orgParentId], ['name_th' => $divName, 'parent_id' => $orgParentId, 'level' => 3, 'org_type' => 'division', 'code' => 'DIV-'.uniqid()]);
            $orgParentId = $divId;
        }
        // Section
        $sectId = null;
        if ($sectName && $orgParentId) {
            $sectData = ['name_th' => $sectName, 'parent_id' => $orgParentId, 'level' => 4, 'org_type' => 'section', 'code' => 'SEC-'.uniqid()];
            // Handle `region`
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
        }

        // 9. Location
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

        // 11. Budget Line Item
        $lineRegion = 'central'; 
        if ($regionType) {
             $map = ['ส่วนกลาง'=>'central', 'ส่วนภูมิภาค'=>'regional', 'จังหวัด'=>'provincial'];
             if (isset($map[$regionType])) $lineRegion = $map[$regionType];
             if ($regionType == 'ส่วนกลางที่ตั้งอยู่ในภูมิภาค') $lineRegion = 'central';
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
    echo "<h2 style='color:green'>Import Completed successfully! Rows processed: $rowCount</h2>";

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "<h2 style='color:red'>Error: " . $e->getMessage() . "</h2>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
