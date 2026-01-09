<?php
/**
 * Debug Router - More complete test
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bootstrap the app
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->load();

echo "<h1>Router Debug - Complete</h1>";
echo "<pre>";

// 1. Show $SERVER values
echo "=== SERVER VALUES ===\n";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "dirname(SCRIPT_NAME): " . dirname($_SERVER['SCRIPT_NAME']) . "\n";
echo "\n";

// Simulate Router::dispatch() logic
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "=== URI PARSING ===\n";
echo "parse_url result: $uri\n";

$scriptName = dirname($_SERVER['SCRIPT_NAME']);
echo "Script name dir: $scriptName\n";

if ($scriptName !== '/' && $scriptName !== '\\') {
    $uriStripped = substr($uri, strlen($scriptName));
    echo "After stripping base: $uriStripped\n";
} else {
    $uriStripped = $uri;
    echo "No stripping (root): $uriStripped\n";
}

// Normalize
$uriNormalized = '/' . trim($uriStripped, '/');
if ($uriNormalized !== '/') {
    $uriNormalized = rtrim($uriNormalized, '/');
}
echo "Normalized URI: $uriNormalized\n\n";

// 2. Load routes and check
echo "=== LOADING ROUTES ===\n";
require_once __DIR__ . '/../routes/web.php';

// Get routes via reflection
$routerClass = new ReflectionClass(\App\Core\Router::class);
$routesProp = $routerClass->getProperty('routes');
$routesProp->setAccessible(true);
$routes = $routesProp->getValue();

echo "Total routes: " . count($routes) . "\n\n";

echo "=== ROUTES CONTAINING 'execution' ===\n";
foreach ($routes as $route) {
    if (stripos($route['path'], 'execution') !== false) {
        echo "  Method: " . $route['method'] . "\n";
        echo "  Path: " . $route['path'] . "\n";
        echo "  Pattern: " . $route['pattern'] . "\n";
        echo "  ---\n";
    }
}

echo "\n=== TESTING PATTERN MATCH ===\n";
foreach ($routes as $route) {
    if ($route['path'] === '/execution') {
        echo "Testing pattern: " . $route['pattern'] . "\n";
        echo "Against URI: $uriNormalized\n";
        $matches = preg_match($route['pattern'], $uriNormalized);
        echo "Match result: " . ($matches ? 'YES' : 'NO') . "\n";
    }
}

echo "\n=== FIRST 10 ROUTES ===\n";
foreach (array_slice($routes, 0, 10) as $i => $route) {
    echo "$i. [{$route['method']}] {$route['path']} => {$route['pattern']}\n";
}

echo "</pre>";
