<?php

$source = 'C:/laragon/www/hr_budget';
$dest = 'C:/laragon/www/hr_budget/archives/backup/2025-12-27_full_project_backup';
$exclude = ['.git', '.idea', '.vscode', 'archives', 'vendor', 'node_modules'];

function recursiveCopy($src, $dst, $exclude) {
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if (in_array($file, $exclude)) {
                echo "Skipping: $file\n";
                continue;
            }
            if ( is_dir($src . '/' . $file) ) {
                recursiveCopy($src . '/' . $file, $dst . '/' . $file, $exclude);
            }
            else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

echo "Starting backup...\n";
echo "Source: $source\n";
echo "Destination: $dest\n";

if (!file_exists($source)) {
    die("Source directory not found!\n");
}

recursiveCopy($source, $dest, $exclude);

echo "Backup completed successfully.\n";
?>
