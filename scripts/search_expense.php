<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\Database;
$pdo = new PDO('mysql:host=localhost;dbname=hr_budget;charset=utf8mb4', 'root', '');

$log = "Searching results:\n";

// 1. Search Groups
$stmt = $pdo->prepare("SELECT * FROM expense_groups WHERE name_th LIKE ?");
$stmt->execute(['%ค่าใช้สอย%']);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($groups as $g) {
    $log .= "Group: {$g['name_th']} (ID: {$g['id']})\n";
}

// 2. Search Items
$stmt = $pdo->prepare("SELECT * FROM expense_items WHERE name_th LIKE ?");
$stmt->execute(['%ค่าใช้สอย%']);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($items as $i) {
    $log .= "Item: {$i['name_th']} (ID: {$i['id']}) GroupID: {$i['expense_group_id']} Lvl: {$i['level']} Parent: {$i['parent_id']}\n";
}

file_put_contents('result.txt', $log);
echo "Done.";
