<?php
// Reset OPcache and reload specific files
header('Content-Type: text/plain; charset=utf-8');

echo "=== OPcache Reset ===\n\n";

// Check if OPcache is enabled
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "OPcache is enabled.\n";
        echo "Cached scripts: " . $status['opcache_statistics']['num_cached_scripts'] . "\n\n";
        
        // Invalidate specific files
        $files = [
            __DIR__ . '/../src/Models/Project.php',
            __DIR__ . '/../src/Models/Activity.php',
            __DIR__ . '/../src/Core/Model.php',
        ];
        
        foreach ($files as $file) {
            if (file_exists($file)) {
                $realPath = realpath($file);
                if (opcache_invalidate($realPath, true)) {
                    echo "Invalidated: $realPath\n";
                } else {
                    echo "Failed to invalidate: $realPath\n";
                }
            } else {
                echo "File not found: $file\n";
            }
        }
        
        echo "\n";
        
        // Full reset
        if (opcache_reset()) {
            echo "OPcache fully reset!\n";
        } else {
            echo "Failed to reset OPcache.\n";
        }
    } else {
        echo "OPcache is disabled.\n";
    }
} else {
    echo "OPcache extension not loaded.\n";
}

echo "\n=== Now testing Project model ===\n";

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $projects = \App\Models\Project::where('plan_id', 1)->get();
    echo "OK: Found " . count($projects) . " projects\n";
} catch (Error $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}
