<?php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getPdo();

echo "Updating category sort order...\n";

// Set งบบุคลากร first
$db->exec("UPDATE budget_categories SET sort_order = 1 WHERE name_th LIKE '%บุคลากร%'");
echo "1. งบบุคลากร = sort_order 1\n";

// Set งบดำเนินงาน second
$db->exec("UPDATE budget_categories SET sort_order = 2 WHERE name_th LIKE '%ดำเนินงาน%'");
echo "2. งบดำเนินงาน = sort_order 2\n";

// Verify
echo "\nVerification:\n";
$stmt = $db->query("SELECT id, name_th, sort_order FROM budget_categories ORDER BY sort_order ASC");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "  {$row['sort_order']}. {$row['name_th']} (ID: {$row['id']})\n";
}

echo "\nDone!\n";
