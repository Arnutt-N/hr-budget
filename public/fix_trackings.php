<?php
/**
 * HTTP Migration: Fix budget_trackings table
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

header('Content-Type: application/json');

$result = [
    'success' => false,
    'steps' => []
];

try {
    $db = Database::getInstance();
    
    // Step 1: Check for duplicates
    $sql = "SELECT fiscal_year, budget_category_item_id, COUNT(*) as cnt 
            FROM budget_trackings 
            GROUP BY fiscal_year, budget_category_item_id 
            HAVING cnt > 1";
    $duplicates = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    
    $result['steps'][] = [
        'step' => 'Check Duplicates',
        'found' => count($duplicates)
    ];
    
    if (!empty($duplicates)) {
        // Step 2: Delete duplicates (keep latest ID)
        $deleteSql = "DELETE t1 FROM budget_trackings t1
                      INNER JOIN budget_trackings t2 
                      WHERE t1.id < t2.id 
                        AND t1.fiscal_year = t2.fiscal_year 
                        AND t1.budget_category_item_id = t2.budget_category_item_id";
        $stmt = $db->prepare($deleteSql);
        $stmt->execute();
        $deleted = $stmt->rowCount();
        $result['steps'][] = [
            'step' => 'Delete Duplicates',
            'deleted' => $deleted
        ];
    }
    
    // Step 3: Check if unique index already exists
    $indexes = $db->query("SHOW INDEX FROM budget_trackings WHERE Key_name = 'unique_tracking'")->fetchAll();
    
    if (!empty($indexes)) {
        $result['steps'][] = [
            'step' => 'Check Index',
            'status' => 'Already exists'
        ];
    } else {
        // Step 4: Add Unique Index
        $db->exec("ALTER TABLE budget_trackings ADD UNIQUE KEY unique_tracking (fiscal_year, budget_category_item_id)");
        $result['steps'][] = [
            'step' => 'Add Unique Index',
            'status' => 'Created'
        ];
    }
    
    $result['success'] = true;
    $result['message'] = 'Migration complete. ON DUPLICATE KEY UPDATE will now work correctly.';

} catch (Exception $e) {
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
