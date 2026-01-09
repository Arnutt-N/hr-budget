<?php
// Simple DB Verify
try {
    $pdo = new PDO('mysql:host=localhost;dbname=hr_budget;charset=utf8mb4', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SHOW TABLES LIKE 'budget_plans'");
    $exists = $stmt->rowCount() > 0;
    
    $status = $exists ? "TABLE EXISTS" : "TABLE DROPPED";
    file_put_contents('verification_result.txt', $status);
    echo $status;
    
} catch (Exception $e) {
    file_put_contents('verification_result.txt', "ERROR: " . $e->getMessage());
    echo "ERROR: " . $e->getMessage();
}
