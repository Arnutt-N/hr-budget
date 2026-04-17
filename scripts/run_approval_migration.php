<?php
/**
 * Quick Migration: Create notifications table
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Connected to database: {$_ENV['DB_NAME']}\n";

    // Read and execute the migration
    $sql = file_get_contents(__DIR__ . '/../database/migrations/060_approval_workflow.sql');
    $pdo->exec($sql);

    echo "✅ Migration 060_approval_workflow.sql executed successfully!\n";
    echo "Tables created: approval_settings, approvers, notifications\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
