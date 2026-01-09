<?php
/**
 * Debug Dashboard - Path diagnostics first (no exceptions)
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Dashboard Debug - Path Check</h1>";
echo "<pre>";

// Test 1: Basic path operations
echo "=== PATH TESTS ===\n\n";
echo "__DIR__ = " . __DIR__ . "\n";
echo "dirname(__DIR__) = " . dirname(__DIR__) . "\n\n";

$basePath = dirname(__DIR__);
$envFile = $basePath . DIRECTORY_SEPARATOR . '.env';
echo "ENV path (with DIRECTORY_SEPARATOR) = $envFile\n\n";

// Test 2: File exists with different slashes
echo "=== FILE EXISTS TESTS ===\n";
$paths = [
    $basePath . '/.env',
    $basePath . '\\.env',
    $basePath . DIRECTORY_SEPARATOR . '.env',
    'C:/laragon/www/hr_budget/.env',
    'C:\\laragon\\www\\hr_budget\\.env',
    realpath($basePath) . '/.env',
];
foreach ($paths as $path) {
    $exists = @file_exists($path);
    echo "$path => " . ($exists ? 'EXISTS' : 'NOT FOUND') . "\n";
}

// Test 3: Directory scan - what files does PHP see?
echo "\n=== DIRECTORY SCAN ===\n";
echo "Contents of $basePath:\n";
$files = @scandir($basePath);
if ($files) {
    $found = [];
    foreach ($files as $f) {
        if ($f[0] === '.' || $f === 'composer.json' || $f === 'README.md') {
            $found[] = $f;
        }
    }
    echo "Hidden & key files: " . implode(', ', $found) . "\n";
    echo "Has .env? " . (in_array('.env', $files) ? 'YES' : 'NO') . "\n";
    echo "Has .env.example? " . (in_array('.env.example', $files) ? 'YES' : 'NO') . "\n";
} else {
    echo "ERROR: Could not scan directory!\n";
}

// Test 4: Does .env.example exist and readable?
echo "\n=== .ENV.EXAMPLE TEST ===\n";
$examplePath = $basePath . '/.env.example';
if (file_exists($examplePath)) {
    echo "file_exists: YES\n";
    echo "is_readable: " . (is_readable($examplePath) ? 'YES' : 'NO') . "\n";
} else {
    echo "file_exists: NO\n";
}

// Test 5: Try reading .env directly with PHP
echo "\n=== DIRECT FILE READ TEST ===\n";
$envPath = $basePath . '/.env';
$content = @file_get_contents($envPath);
if ($content !== false) {
    echo "file_get_contents: SUCCESS\n";
    echo "Content preview (first 100 chars):\n";
    echo substr($content, 0, 100) . "...\n";
} else {
    echo "file_get_contents: FAILED\n";
    $err = error_get_last();
    echo "Error: " . ($err['message'] ?? 'unknown') . "\n";
}

// Test 6: NOW try autoloader and dotenv
echo "\n=== DOTENV TEST (LAST) ===\n";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    echo "Autoloader: OK\n";
    
    echo "Attempting Dotenv::createImmutable('$basePath')...\n";
    $dotenv = Dotenv\Dotenv::createImmutable($basePath);
    $dotenv->load();
    echo "Dotenv: SUCCESS!\n";
    echo "DB_HOST = " . ($_ENV['DB_HOST'] ?? 'not set') . "\n";
    echo "DB_DATABASE = " . ($_ENV['DB_DATABASE'] ?? 'not set') . "\n";
} catch (Throwable $e) {
    echo "Dotenv: FAILED\n";
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== END ===\n";
echo "</pre>";
