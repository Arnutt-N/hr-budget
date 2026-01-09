<?php
$path = 'C:/laragon/www/hr_budget/archives/backup/2025-12-27_full_project_backup';
echo "Attempting to create: $path\n";
if (mkdir($path, 0777, true)) {
    echo "Success: Directory created.\n";
} else {
    $error = error_get_last();
    echo "Failed: " . print_r($error, true) . "\n";
    if (file_exists($path)) {
        echo "But file_exists says it exists!\n";
    }
}
?>
