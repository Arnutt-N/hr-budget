<?php
header('Content-Type: text/plain; charset=utf-8');
mb_internal_encoding("UTF-8");

$logFile = __DIR__ . '/../migration_log.txt';
function logMsg($msg) {
    global $logFile;
    file_put_contents($logFile, $msg . "\n", FILE_APPEND);
}

logMsg("Starting migration...");

$host = '127.0.0.1';
$db   = 'hr_budget';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    logMsg("Connected successfully");
    
    // 1. Find the Expense Group
    $groupNamePartial = "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ";
    
    $stmt = $pdo->prepare("SELECT * FROM expense_groups WHERE name_th LIKE ?");
    $stmt->execute(["%$groupNamePartial%"]);
    $group = $stmt->fetch();
    
    if (!$group) {
        logMsg("Error: Expense Group not found for '$groupNamePartial'.");
        exit;
    }
    
    logMsg("Found Group: [{$group['id']}] {$group['name_th']}");
    
    // 2. Define Items to Insert
    $items = [
        "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์",
        "ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก"
    ];
    
    foreach ($items as $itemName) {
        // Check if exists
        $check = $pdo->prepare("SELECT id FROM expense_items WHERE expense_group_id = ? AND name_th = ?");
        $check->execute([$group['id'], $itemName]);
        $existing = $check->fetch();
        
        if ($existing) {
            logMsg(" - Item already exists: $itemName (ID: {$existing['id']})");
        } else {
            // Insert
            $insert = $pdo->prepare("INSERT INTO expense_items (expense_group_id, name_th, is_active, sort_order) VALUES (?, ?, 1, 99)");
            $insert->execute([$group['id'], $itemName]);
            logMsg(" - Inserted: $itemName (ID: " . $pdo->lastInsertId() . ")");
        }
    }
    
    logMsg("Done.");
    
} catch (\PDOException $e) {
    logMsg("Database Error: " . $e->getMessage());
}
