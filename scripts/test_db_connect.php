<?php
try {
    // Laragon default credential for test - FORCE 127.0.0.1
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=hr_budget;port=3306', 'root', '');
    echo "✅ Connection Successful (127.0.0.1)\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'hr_budget'");
    echo "Table Count: " . $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "❌ Connection Failed: " . $e->getMessage() . "\n";
}
