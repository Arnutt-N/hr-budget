<?php
/**
 * Simulate accessing /execution via index.php
 * This shows what the Router sees
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Simulate /execution Request</h1>";
echo "<pre>";

// Manually set the REQUEST_URI to simulate /execution
$original_uri = $_SERVER['REQUEST_URI'];
$_SERVER['REQUEST_URI'] = '/hr_budget/public/execution';

echo "=== SIMULATING /execution ACCESS ===\n";
echo "Original REQUEST_URI: $original_uri\n";
echo "Simulated REQUEST_URI: {$_SERVER['REQUEST_URI']}\n";
echo "SCRIPT_NAME: {$_SERVER['SCRIPT_NAME']}\n\n";

// Bootstrap
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();
date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Bangkok');
\App\Core\Auth::init();

// Load routes
require __DIR__ . '/../routes/web.php';

// Now test the Router URL parsing logic
echo "=== ROUTER URL PARSING ===\n";
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "parse_url: $uri\n";

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
echo "dirname(SCRIPT_NAME): $scriptName\n";

if ($scriptName !== '/' && $scriptName !== '\\') {
    $uri = substr($uri, strlen($scriptName));
}
echo "After stripping: $uri\n";

$uri = '/' . trim($uri, '/');
if ($uri !== '/') {
    $uri = rtrim($uri, '/');
}
echo "Normalized URI: $uri\n\n";

// Get routes
$routerClass = new ReflectionClass(\App\Core\Router::class);
$routesProp = $routerClass->getProperty('routes');
$routesProp->setAccessible(true);
$routes = $routesProp->getValue();

echo "=== MATCHING ROUTES ===\n";
foreach ($routes as $route) {
    if (preg_match($route['pattern'], $uri)) {
        echo "MATCH: [{$route['method']}] {$route['path']}\n";
    }
}

echo "\n=== /execution ROUTE CHECK ===\n";
foreach ($routes as $route) {
    if (strpos($route['path'], 'execution') !== false) {
        echo "Route: [{$route['method']}] {$route['path']}\n";
        echo "Pattern: {$route['pattern']}\n";
        echo "Match result: " . (preg_match($route['pattern'], $uri) ? 'YES' : 'NO') . "\n\n";
    }
}

echo "</pre>";
