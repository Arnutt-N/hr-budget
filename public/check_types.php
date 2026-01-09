<?php
// C:\laragon\www\hr_budget\public\check_types.php
require_once __DIR__ . '/../vendor/autoload.php';
use App\Core\Database;

$db = Database::getInstance();
$types = Database::query("SELECT id, name_th FROM expense_types ORDER BY id");

foreach($types as $t) {
    echo "ID: {$t['id']} - Name: {$t['name_th']}<br>";
}
