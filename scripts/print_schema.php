<?php
$config = include 'config/database.php';
$pdo = new PDO("mysql:host={$config['host']};dbname={$config['database']}", $config['username'], $config['password']);
foreach (['budget_allocations', 'budget_line_items'] as $table) {
    echo "[$table]\n";
    $q = $pdo->query("DESC $table");
    while ($r = $q->fetch(PDO::FETCH_ASSOC)) {
        echo $r['Field'] . "\n";
    }
}
