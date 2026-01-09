<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance();

    // 1. Find the Division ID
    echo "--- Finding Division ---\n";
    $divisionName = 'กองบริหารทรัพยากรบุคคล';
    $sql = "SELECT * FROM organizations WHERE name_th LIKE ?";
    $org = $db->queryOne($sql, ["%{$divisionName}%"]);

    if (!$org) {
        echo "Organization '{$divisionName}' not found.\n";
        exit;
    }

    echo "Found Organization: {$org['name_th']} (ID: {$org['id']})\n";
    $orgId = $org['id'];

    // 2. Check Budget Line Items
    echo "\n--- Budget Line Items ---\n";
    $sql = "SELECT DISTINCT activity_id FROM budget_line_items WHERE division_id = ?";
    $rows = $db->query($sql, [$orgId]);
    
    echo "Found " . count($rows) . " distinct activities linked to this division.\n";
    $activityIds = array_column($rows, 'activity_id');

    if (empty($activityIds)) {
        echo "WARNING: No activities found! 'skipFiltering' will likely trigger and show ALL plans.\n";
    } else {
        echo "Activity IDs: " . implode(', ', $activityIds) . "\n";
    }

    // 3. Check what Plans these activities belong to
    if (!empty($activityIds)) {
        echo "\n--- Parent Plans ---\n";
        $placeholders = implode(',', array_fill(0, count($activityIds), '?'));
        
        $sql = "SELECT p.id, p.name_th, p.plan_type, parent.name_th as parent_name 
                FROM budget_plans p 
                LEFT JOIN budget_plans parent ON p.parent_id = parent.id
                WHERE p.id IN ($placeholders)";
        
        $plans = $db->query($sql, $activityIds);
        
        foreach ($plans as $p) {
            echo "- [Activity] {$p['name_th']} (Parent: {$p['parent_name']})\n";
        }
    }

    // 4. Count Total Root Plans (to check if fallback shows everything)
    echo "\n--- Total Root Plans ---\n";
    $sql = "SELECT count(*) as count FROM budget_plans WHERE level = 1";
    $total = $db->queryOne($sql);
    echo "Total Level 1 Plans in DB: {$total['count']}\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
