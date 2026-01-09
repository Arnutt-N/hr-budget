<?php
// scripts/import_budget_structure.php
$config = require __DIR__ . '/../config/database.php';

$host = $config['host'];
$dbname = $config['database'];
$username = $config['username'];
$password = $config['password'];
$charset = $config['charset'];

try {
    // Database connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=$charset", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database.\n";
    
    // Read CSV
    $csvFile = __DIR__ . '/../research/budget_structure_reference.csv';
    if (!file_exists($csvFile)) {
        die("CSV file not found: $csvFile\n");
    }
    
    $file = fopen($csvFile, 'r');
    $header = fgetcsv($file); // Skip header

    $rowCount = 0;
    $pdo->beginTransaction();

    // Cache
    $cache = [
        'expense_types' => [],
        'expense_groups' => [],
        'plans' => [],
        'projects' => [],
        'activities' => []
    ];

    // Pre-load Expense Types
    $stmt = $pdo->query("SELECT id, name_th FROM expense_types");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cache['expense_types'][$row['name_th']] = $row['id'];
    }
    
    // Pre-load Expense Groups
    $stmt = $pdo->query("SELECT id, name_th FROM expense_groups");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $cache['expense_groups'][$row['name_th']] = $row['id'];
    }

    echo "Loaded " . count($cache['expense_types']) . " expense types and " . count($cache['expense_groups']) . " expense groups.\n";

    while (($row = fgetcsv($file)) !== false) {
        $rowCount++;
        // Map columns
        $budgetTypeName = trim($row[0]);
        $planName = trim($row[1]);
        $projectName = trim($row[2]);
        $activityName = trim($row[3]);
        $expenseTypeName = trim($row[4]);
        
        // Item Levels: รายการ 0 - 5
        $items = [
            trim($row[5]),
            trim($row[6]),
            trim($row[7]),
            trim($row[8]),
            trim($row[9]),
            trim($row[10])
        ];

        // 1. Budget Type (Only needed if Plan/Project handling uses it)
        $budgetTypeCode = '';
        if (strpos($budgetTypeName, 'บุคลากร') !== false) $budgetTypeCode = 'BUK';
        elseif (strpos($budgetTypeName, 'บูรณาการ') !== false) $budgetTypeCode = 'INTEG';
        else $budgetTypeCode = 'UNIT';
        
        // Lookup ID (Assuming types exist)
        $stmt = $pdo->prepare("SELECT id FROM budget_types WHERE code = ?");
        $stmt->execute([$budgetTypeCode]);
        $budgetTypeId = $stmt->fetchColumn();

        if (!$budgetTypeId) {
            echo "Warning: Budget Type '$budgetTypeCode' not found. Skipping row $rowCount.\n";
            continue;
        }

        // 2. Plan
        if ($planName) {
            $key = $planName . '|' . $budgetTypeId;
            if (!isset($cache['plans'][$key])) {
                $stmt = $pdo->prepare("SELECT id FROM plans WHERE name_th = ? AND budget_type_id = ?");
                $stmt->execute([$planName, $budgetTypeId]);
                $planId = $stmt->fetchColumn();
                
                if (!$planId) {
                    $code = 'PLAN-' . substr(md5($planName), 0, 8);
                    $stmt = $pdo->prepare("INSERT INTO plans (budget_type_id, name_th, code) VALUES (?, ?, ?)");
                    $stmt->execute([$budgetTypeId, $planName, $code]);
                    $planId = $pdo->lastInsertId();
                }
                $cache['plans'][$key] = $planId;
            }
            $planId = $cache['plans'][$key];

            // 3. Project
            if ($projectName) {
                $key = $projectName . '|' . $planId;
                if (!isset($cache['projects'][$key])) {
                    $stmt = $pdo->prepare("SELECT id FROM projects WHERE name_th = ? AND plan_id = ?");
                    $stmt->execute([$projectName, $planId]);
                    $projectId = $stmt->fetchColumn();
                    
                    if (!$projectId) {
                        $stmt = $pdo->prepare("INSERT INTO projects (plan_id, name_th) VALUES (?, ?)");
                        $stmt->execute([$planId, $projectName]);
                        $projectId = $pdo->lastInsertId();
                    }
                    $cache['projects'][$key] = $projectId;
                }
                $projectId = $cache['projects'][$key];

                // 4. Activity
                if ($activityName) {
                    $key = $activityName . '|' . $projectId;
                    if (!isset($cache['activities'][$key])) {
                        $stmt = $pdo->prepare("SELECT id FROM activities WHERE name_th = ? AND project_id = ?");
                        $stmt->execute([$activityName, $projectId]);
                        $actId = $stmt->fetchColumn();
                        
                        if (!$actId) {
                            $stmt = $pdo->prepare("INSERT INTO activities (project_id, name_th) VALUES (?, ?)");
                            $stmt->execute([$projectId, $activityName]);
                            $actId = $pdo->lastInsertId();
                        }
                        $cache['activities'][$key] = $actId;
                    }
                }
            }
        }

        // 5. Expense Items
        $expenseTypeId = $cache['expense_types'][$expenseTypeName] ?? null;
        if (!$expenseTypeId) {
             // Try partial match
             foreach ($cache['expense_types'] as $name => $id) {
                if (strpos($expenseTypeName, $name) !== false) {
                    $expenseTypeId = $id;
                    break;
                }
            }
        }
        
        if ($expenseTypeId) {
            // Determine Expense Group
            $item0 = $items[0];
            $expenseGroupId = null;
            $startLevel = 0;
            
            if (isset($cache['expense_groups'][$item0])) {
                $expenseGroupId = $cache['expense_groups'][$item0];
                $startLevel = 1;
            } else {
                // Fallback: Find first group of this type
                $stmt = $pdo->prepare("SELECT id FROM expense_groups WHERE expense_type_id = ? LIMIT 1");
                $stmt->execute([$expenseTypeId]);
                $expenseGroupId = $stmt->fetchColumn();
                $startLevel = 0;
            }

            if (!$expenseGroupId) {
                echo "Warning: No Expense Group found for Type '$expenseTypeName'. Skipping items for row $rowCount.\n";
                // Optionally: Create a default group? No, skipping is safer.
                continue;
            }

            // Process Items
            $parentId = null;
            for ($i = $startLevel; $i < 6; $i++) {
                $itemName = $items[$i];
                if (empty($itemName)) break;

                // Check duplicate
                $sql = "SELECT id FROM expense_items WHERE name_th = :name AND expense_group_id = :group AND level = :level";
                $params = [
                    ':name' => $itemName,
                    ':group' => $expenseGroupId,
                    ':level' => ($i - $startLevel)
                ];
                
                if ($parentId) {
                    $sql .= " AND parent_id = :parent";
                    $params[':parent'] = $parentId;
                } else {
                    $sql .= " AND parent_id IS NULL";
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $itemId = $stmt->fetchColumn();

                if (!$itemId) {
                    $insSql = "INSERT INTO expense_items (expense_group_id, parent_id, name_th, level) VALUES (:group, :parent, :name, :level)";
                    $insParams = [
                        ':group' => $expenseGroupId,
                        ':parent' => $parentId,
                        ':name' => $itemName,
                        ':level' => ($i - $startLevel)
                    ];
                    $stmt = $pdo->prepare($insSql);
                    $stmt->execute($insParams);
                    $itemId = $pdo->lastInsertId();
                }
                
                $parentId = $itemId;
            }
        } else {
             // echo "Warning: Expense Type '$expenseTypeName' not found. Skipping items row $rowCount.\n";
        }
    }
    
    $pdo->commit();
    echo "Import script finished. Processed $rowCount rows.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}
