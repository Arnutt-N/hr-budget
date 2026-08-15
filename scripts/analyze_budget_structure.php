<?php
/**
 * Analyze budget_structure_reference.csv for hierarchy patterns
 * 
 * This script will:
 * 1. Count total rows
 * 2. Identify unique values for each hierarchy level (รายการ 0-5)
 * 3. Analyze depth distribution (which rows go to which level)
 * 4. Show category mapping (ประเภทรายจ่าย)
 * 5. Generate summary statistics
 */

$csvFile = __DIR__ . '/../database/budget_structure_reference.csv';

if (!file_exists($csvFile)) {
    die("Error: CSV file not found at $csvFile\n");
}

echo "=================================================\n";
echo "Budget Structure CSV Analysis\n";
echo "=================================================\n\n";

// Read CSV
$handle = fopen($csvFile, 'r');
if (!$handle) {
    die("Error: Could not open CSV file\n");
}

// Get header
$header = fgetcsv($handle);
echo "📊 Columns Found: " . count($header) . "\n";
echo "Column Names:\n";
foreach ($header as $idx => $col) {
    echo "  [$idx] $col\n";
}
echo "\n";

// Column mapping (based on the CSV structure)
$colMap = [
    'budget_type' => 0,          // ประเภทงบประมาณ
    'plan' => 1,                 // แผนงาน
    'product_project' => 2,      // ผลผลิต/โครงการ
    'activity' => 3,             // กิจกรรม
    'expense_type' => 4,         // ประเภทรายจ่าย
    'item_0' => 5,              // รายการ 0
    'item_1' => 6,              // รายการ 1
    'item_2' => 7,              // รายการ 2
    'item_3' => 8,              // รายการ 3
    'item_4' => 9,              // รายการ 4
    'item_5' => 10,             // รายการ 5
];

// Statistics
$stats = [
    'total_rows' => 0,
    'expense_types' => [],
    'item_levels' => [],
];

// Unique values per level
$uniqueValues = [
    'item_0' => [],
    'item_1' => [],
    'item_2' => [],
    'item_3' => [],
    'item_4' => [],
    'item_5' => [],
];

// Depth distribution (how deep each row goes)
$depthDistribution = [
    0 => 0, // Only รายการ 0
    1 => 0, // Up to รายการ 1
    2 => 0, // Up to รายการ 2
    3 => 0, // Up to รายการ 3
    4 => 0, // Up to รายการ 4
    5 => 0, // Up to รายการ 5
];

// Category to expense type mapping
$categoryMapping = [];

// Read data
$rowNum = 0;
while (($row = fgetcsv($handle)) !== false) {
    $rowNum++;
    $stats['total_rows']++;
    
    // Get expense type (งบบุคลากร, งบดำเนินงาน, etc.)
    $expenseType = isset($row[$colMap['expense_type']]) ? trim($row[$colMap['expense_type']]) : '';
    
    if (!empty($expenseType)) {
        if (!isset($stats['expense_types'][$expenseType])) {
            $stats['expense_types'][$expenseType] = 0;
        }
        $stats['expense_types'][$expenseType]++;
    }
    
    // Determine max depth for this row
    $maxDepth = -1;
    for ($i = 0; $i <= 5; $i++) {
        $itemValue = isset($row[$colMap["item_$i"]]) ? trim($row[$colMap["item_$i"]]) : '';
        
        if (!empty($itemValue)) {
            $maxDepth = $i;
            
            // Store unique value
            $key = $itemValue;
            if (!isset($uniqueValues["item_$i"][$key])) {
                $uniqueValues["item_$i"][$key] = 0;
            }
            $uniqueValues["item_$i"][$key]++;
        }
    }
    
    // Update depth distribution
    if ($maxDepth >= 0) {
        $depthDistribution[$maxDepth]++;
    }
    
    // Build category mapping
    $item0 = isset($row[$colMap['item_0']]) ? trim($row[$colMap['item_0']]) : '';
    if (!empty($expenseType) && !empty($item0)) {
        if (!isset($categoryMapping[$expenseType])) {
            $categoryMapping[$expenseType] = [];
        }
        if (!in_array($item0, $categoryMapping[$expenseType])) {
            $categoryMapping[$expenseType][] = $item0;
        }
    }
}

fclose($handle);

// Output Results
echo "📈 SUMMARY STATISTICS\n";
echo str_repeat("-", 50) . "\n";
echo "Total Rows: " . $stats['total_rows'] . "\n\n";

echo "🏷️  EXPENSE TYPES (ประเภทรายจ่าย)\n";
echo str_repeat("-", 50) . "\n";
arsort($stats['expense_types']);
foreach ($stats['expense_types'] as $type => $count) {
    echo sprintf("%-30s: %4d rows\n", $type, $count);
}
echo "\n";

echo "📊 HIERARCHY DEPTH DISTRIBUTION\n";
echo str_repeat("-", 50) . "\n";
for ($i = 0; $i <= 5; $i++) {
    $percentage = $stats['total_rows'] > 0 ? ($depthDistribution[$i] / $stats['total_rows'] * 100) : 0;
    echo sprintf("Rows reaching Level %d: %4d (%.1f%%)\n", $i, $depthDistribution[$i], $percentage);
}
echo "\n";

echo "🔢 UNIQUE VALUES PER LEVEL\n";
echo str_repeat("-", 50) . "\n";
for ($i = 0; $i <= 5; $i++) {
    $count = count($uniqueValues["item_$i"]);
    echo sprintf("รายการ %d: %4d unique values\n", $i, $count);
}
echo "\n";

echo "🗺️  CATEGORY → ITEM 0 MAPPING\n";
echo str_repeat("-", 50) . "\n";
foreach ($categoryMapping as $category => $items) {
    echo "$category:\n";
    foreach ($items as $item) {
        echo "  - $item\n";
    }
    echo "\n";
}

echo "✨ TOP 10 MOST COMMON VALUES (Each Level)\n";
echo str_repeat("=", 50) . "\n\n";
for ($i = 0; $i <= 5; $i++) {
    echo "รายการ $i:\n";
    arsort($uniqueValues["item_$i"]);
    $top10 = array_slice($uniqueValues["item_$i"], 0, 10, true);
    foreach ($top10 as $value => $count) {
        echo sprintf("  %-50s: %3d occurrences\n", mb_substr($value, 0, 50), $count);
    }
    echo "\n";
}

echo "\n=================================================\n";
echo "Analysis Complete!\n";
echo "=================================================\n";
