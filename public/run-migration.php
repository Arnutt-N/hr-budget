<?php
require_once __DIR__ . '/../vendor/autoload.php';
$config = require __DIR__ . '/../config/app.php';
use App\Core\Database;

$sql = file_get_contents(__DIR__ . '/../database/migrations/007_create_budget_records.sql');
try {
    Database::getInstance()->exec($sql);
    echo "Migration completed successfully.";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage();
}
