<?php
/**
 * Backup Script - Copy project files to phase directories
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$basePath = __DIR__ . '/../';
$backupBase = $basePath . 'archives/backup/';

// Define what to backup for each phase
$phases = [
    'phase1' => [
        'src' => 'src',
        'config' => 'config',
        'database' => 'database'
    ],
    'phase2' => [
        'src' => 'src',
        'views' => 'resources/views'
    ],
    'phase3' => [
        'src' => 'src',
        'views' => 'resources/views',
        'database' => 'database',
        'scripts' => 'scripts'
    ]
];

$result = ['success' => true, 'phases' => []];

function recursiveCopy($src, $dst) {
    $count = 0;
    if (!is_dir($dst)) {
        mkdir($dst, 0755, true);
    }
    
    $dir = opendir($src);
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') continue;
        
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        
        if (is_dir($srcPath)) {
            $count += recursiveCopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
            $count++;
        }
    }
    closedir($dir);
    return $count;
}

foreach ($phases as $phase => $folders) {
    $phaseResult = ['phase' => $phase, 'folders' => []];
    
    foreach ($folders as $destName => $srcPath) {
        $src = realpath($basePath . $srcPath);
        $dst = $backupBase . $phase . '/' . $destName;
        
        if ($src && is_dir($src)) {
            $copied = recursiveCopy($src, $dst);
            $phaseResult['folders'][$destName] = ['copied' => $copied];
        } else {
            $phaseResult['folders'][$destName] = ['error' => 'Source not found: ' . $srcPath];
        }
    }
    
    $result['phases'][$phase] = $phaseResult;
}

echo json_encode($result, JSON_PRETTY_PRINT);
