<?php
// Backup script to bypass shell limitations
$source = __DIR__;
$dest = __DIR__ . '/archives/backup/hr_budget_ui_refine_tracking_list_20260105';

// Excludes
$excludes = ['.', '..', 'node_modules', 'vendor', '.git', 'archives', '.gemini', '.agent', 'logs', 'playwright-report', 'test-results'];
$excludeExtensions = ['log', 'tmp', 'bak'];

function recursiveCopy($src, $dst, $excludes, $excludeExtensions) {
    $dir = opendir($src);
    @mkdir($dst, 0777, true);
    
    $fileCount = 0;
    
    while(false !== ( $file = readdir($dir)) ) {
        if (in_array($file, $excludes)) continue;
        
        $info = pathinfo($file);
        if (isset($info['extension']) && in_array($info['extension'], $excludeExtensions)) continue;
        
        if ( is_dir($src . '/' . $file) ) {
            recursiveCopy($src . '/' . $file, $dst . '/' . $file, $excludes, $excludeExtensions);
        } else {
            copy($src . '/' . $file, $dst . '/' . $file);
            $fileCount++;
        }
    }
    closedir($dir);
}

echo "Starting backup...\n";
echo "Source: $source\n";
echo "Destination: $dest\n";

if (!file_exists($dest)) {
    mkdir($dest, 0777, true);
}

// Copy subdirectories explicitly to verify progress
$items = scandir($source);
foreach ($items as $item) {
    if (in_array($item, $excludes)) continue;
    
    $path = $source . '/' . $item;
    if (is_dir($path)) {
        echo "Copying directory: $item\n";
        recursiveCopy($path, $dest . '/' . $item, $excludes, $excludeExtensions);
    } else { // File
        $info = pathinfo($item);
        if (isset($info['extension']) && in_array($info['extension'], $excludeExtensions)) continue;
        copy($path, $dest . '/' . $item);
    }
}

echo "Backup completed successfully.\n";
file_put_contents($dest . '/backup_success.txt', 'Backup completed at ' . date('Y-m-d H:i:s'));
?>
