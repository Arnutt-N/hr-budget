<?php
// Backup Script for UI Refinements Session
// Date: 2025-12-19

$backupDir = __DIR__ . '/archives/backup/2025-12-19_ui-refinements/';

// Create backup directory if not exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Files to backup
$files = [
    'resources/views/budgets/execution.php',
    'resources/views/layouts/main.php',
    'src/Controllers/BudgetExecutionController.php',
    'src/Models/BudgetExecution.php',
    'project-log-md/2025-12-19_budget-execution-ui-refinements.md'
];

echo "Starting backup...\n\n";

foreach ($files as $file) {
    $source = __DIR__ . '/' . $file;
    $filename = basename($file);
    $destination = $backupDir . $filename;
    
    if (file_exists($source)) {
        if (copy($source, $destination)) {
            echo "✓ Copied: $file\n";
        } else {
            echo "✗ Failed: $file\n";
        }
    } else {
        echo "✗ Not found: $file\n";
    }
}

echo "\n✅ Backup completed!\n";
echo "Location: $backupDir\n";
