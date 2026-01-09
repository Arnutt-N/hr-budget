<?php
/**
 * Fix Column Names - Rename PDF to Excel/CSV columns
 */

$host = '127.0.0.1';
$db   = 'hr_budget';
$user = 'root';
$pass = ''; 

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4;port=3306";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "🔧 Fixing Column Names...\n\n";

$alterStatements = [
    // fact_budget_execution: datasource_page -> datasource_row
    "ALTER TABLE `fact_budget_execution` CHANGE COLUMN `datasource_page` `datasource_row` INT NULL COMMENT 'หมายเลขแถว Excel/CSV ต้นทาง (Audit Trail)'",
    
    // log_transfer_note: page_number -> source_row
    "ALTER TABLE `log_transfer_note` CHANGE COLUMN `page_number` `source_row` INT NULL COMMENT 'แถว Excel/CSV ที่ปรากฏหมายเหตุ'",
];

foreach ($alterStatements as $sql) {
    try {
        $pdo->exec($sql);
        echo "✅ " . substr($sql, 0, 80) . "...\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Unknown column') !== false) {
            echo "⚠️ Column already renamed (skipped)\n";
        } else {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n✅ Done! Now run: php scripts/seed_dimensional_mockup.php\n";
