<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require 'src/Core/Database.php';

try {
    echo "BUDGET_REQUESTS COLUMNS:\n";
    $res = App\Core\Database::query('SHOW COLUMNS FROM budget_requests');
    foreach($res as $c) echo "- " . $c['Field'] . "\n";

    echo "\nBUDGET_REQUEST_ITEMS COLUMNS:\n";
    $res = App\Core\Database::query('SHOW COLUMNS FROM budget_request_items');
    foreach($res as $c) echo "- " . $c['Field'] . "\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
