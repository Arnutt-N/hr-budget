<?php
/**
 * Web-based Migration Tool
 * Access via: http://localhost/hr_budget/public/migrate.php
 * 
 * This bypasses CLI issues on Windows
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Migration</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #00ff00;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
        }
        .box {
            background: #000;
            border: 2px solid #00ff00;
            padding: 20px;
            margin: 10px 0;
        }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .warning { color: #ffaa00; }
        button {
            background: #00ff00;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
            margin: 10px 0;
        }
        button:hover { background: #00cc00; }
        pre {
            background: #222;
            padding: 10px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <h1>🔧 Database Migration Tool</h1>
    
    <?php
    if (!isset($_GET['action'])) {
        // Show current status
        try {
            $db = Database::getPdo();
            $columns = $db->query("SHOW COLUMNS FROM fact_budget_execution")->fetchAll(PDO::FETCH_COLUMN);
            
            $hasRequestAmount = in_array('request_amount', $columns);
            $hasRecordDate = in_array('record_date', $columns);
            
            echo '<div class="box">';
            echo '<h2>Current Schema Status</h2>';
            echo '<p><strong>Table:</strong> fact_budget_execution</p>';
            echo '<p class="' . ($hasRequestAmount ? 'success' : 'error') . '">request_amount: ' . ($hasRequestAmount ? '✅ EXISTS' : '❌ MISSING') . '</p>';
            echo '<p class="' . ($hasRecordDate ? 'success' : 'error') . '">record_date: ' . ($hasRecordDate ? '✅ EXISTS' : '❌ MISSING') . '</p>';
            echo '</div>';
            
            if (!$hasRequestAmount || !$hasRecordDate) {
                echo '<div class="box warning">';
                echo '<h2>⚠️ Migration Required</h2>';
                echo '<p>The following columns need to be added:</p>';
                echo '<ul>';
                if (!$hasRequestAmount) echo '<li>request_amount (DECIMAL 20,2)</li>';
                if (!$hasRecordDate) echo '<li>record_date (DATE)</li>';
                echo '</ul>';
                echo '<form method="get">';
                echo '<input type="hidden" name="action" value="migrate">';
                echo '<button type="submit">🚀 Run Migration Now</button>';
                echo '</form>';
                echo '</div>';
            } else {
                echo '<div class="box success">';
                echo '<h2>✅ All Columns Exist</h2>';
                echo '<p>No migration needed. Your database is up to date!</p>';
                echo '</div>';
            }
            
            // Show all columns
            echo '<div class="box">';
            echo '<h3>All Columns in fact_budget_execution:</h3>';
            echo '<pre>';
            $result = $db->query("SHOW COLUMNS FROM fact_budget_execution")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $col) {
                printf("%-25s %-20s\n", $col['Field'], $col['Type']);
            }
            echo '</pre>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '<div class="box error">';
            echo '<h2>❌ Database Error</h2>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '</div>';
        }
        
    } elseif ($_GET['action'] === 'migrate') {
        // Run migration
        echo '<div class="box">';
        echo '<h2>Running Migration...</h2>';
        
        try {
            $db = Database::getPdo();
            $columns = $db->query("SHOW COLUMNS FROM fact_budget_execution")->fetchAll(PDO::FETCH_COLUMN);
            
            $executed = [];
            
            // Add request_amount
            if (!in_array('request_amount', $columns)) {
                echo '<p>Adding request_amount column...</p>';
                $db->exec("ALTER TABLE fact_budget_execution ADD COLUMN request_amount DECIMAL(20,2) NULL DEFAULT NULL COMMENT 'ขออนุมัติวงเงิน' AFTER disbursed_amount");
                $executed[] = 'request_amount';
                echo '<p class="success">✅ request_amount added successfully!</p>';
            } else {
                echo '<p class="warning">⚠️ request_amount already exists, skipping</p>';
            }
            
            // Add record_date
            if (!in_array('record_date', $columns)) {
                echo '<p>Adding record_date column...</p>';
                $db->exec("ALTER TABLE fact_budget_execution ADD COLUMN record_date DATE NULL DEFAULT NULL COMMENT 'วันที่บันทึก (สำหรับ filter)' AFTER fiscal_year");
                $executed[] = 'record_date';
                echo '<p class="success">✅ record_date added successfully!</p>';
            } else {
                echo '<p class="warning">⚠️ record_date already exists, skipping</p>';
            }
            
            // Add index
            $indexes = $db->query("SHOW INDEX FROM fact_budget_execution WHERE Key_name = 'idx_record_date'")->fetchAll();
            if (empty($indexes) && in_array('record_date', $executed)) {
                echo '<p>Adding index on record_date...</p>';
                $db->exec("ALTER TABLE fact_budget_execution ADD INDEX idx_record_date (record_date)");
                echo '<p class="success">✅ Index added successfully!</p>';
            }
            
            echo '</div>';
            echo '<div class="box success">';
            echo '<h2>🎉 Migration Complete!</h2>';
            if (!empty($executed)) {
                echo '<p>Added columns: ' . implode(', ', $executed) . '</p>';
            }
            echo '<p><a href="?">← Check Status Again</a></p>';
            echo '<p><a href="/hr_budget/public/budgets">Test /budgets page →</a></p>';
            echo '<p><a href="/hr_budget/public/budgets/list">Test /budgets/list page →</a></p>';
            echo '</div>';
            
        } catch (PDOException $e) {
            echo '</div>';
            echo '<div class="box error">';
            echo '<h2>❌ Migration Failed</h2>';
            echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
            echo '<p><a href="?">← Back</a></p>';
            echo '</div>';
        }
    }
    ?>
    
</body>
</html>
