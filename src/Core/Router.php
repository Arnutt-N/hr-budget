<?php
/**
 * Router Class
 * 
 * Simple routing system for the application
 */

namespace App\Core;

class Router
{
    private static array $routes = [];
    private static string $basePath = '';
    private static array $currentRoute = [];

    /**
     * Set base path for routing
     */
    public static function setBasePath(string $path): void
    {
        self::$basePath = rtrim($path, '/');
    }

    /**
     * Register a GET route
     */
    public static function get(string $path, callable|array $handler): void
    {
        self::addRoute('GET', $path, $handler);
    }

    /**
     * Register a POST route
     */
    public static function post(string $path, callable|array $handler): void
    {
        self::addRoute('POST', $path, $handler);
    }

    /**
     * Register a PUT route
     */
    public static function put(string $path, callable|array $handler): void
    {
        self::addRoute('PUT', $path, $handler);
    }

    /**
     * Register a DELETE route
     */
    public static function delete(string $path, callable|array $handler): void
    {
        self::addRoute('DELETE', $path, $handler);
    }

    /**
     * Add route to collection
     */
    private static function addRoute(string $method, string $path, callable|array $handler): void
    {
        $path = self::$basePath . '/' . trim($path, '/');
        $path = $path === '/' ? '/' : rtrim($path, '/');
        
        // Convert route parameters to regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';
        
        self::$routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    /**
     * Dispatch the request to appropriate handler
     */
    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Determine base path from script location
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        // Normalize slashes for Windows consistency
        $scriptDir = str_replace('\\', '/', $scriptDir);

        // Check if URI starts with the full script directory
        if (strpos($uri, $scriptDir) === 0) {
            $uri = substr($uri, strlen($scriptDir));
        }
        else {
            $parentDir = dirname($scriptDir);
            // Normalize slashes
            $parentDir = str_replace('\\', '/', $parentDir);

            if ($parentDir !== '/' && $parentDir !== '\\' && strpos($uri, $parentDir) === 0) {
                $uri = substr($uri, strlen($parentDir));
            }
        }
        
        // Normalize URI
        $uri = '/' . trim($uri, '/');
        if ($uri !== '/') {
            $uri = rtrim($uri, '/');
        }
        
        // Handle PUT/DELETE via POST with _method field
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                self::$currentRoute = $route;
                
                // Call handler
                self::callHandler($route['handler'], $params);
                return;
            }
        }

        // No route found
        self::notFound();
    }

    /**
     * Call route handler
     */
    private static function callHandler(callable|array $handler, array $params): void
    {
        // String keys would become PHP 8 named arguments and fatal when the
        // route placeholder name differs from the method's parameter name
        // (e.g. {id} vs $categoryId) — params are positional by contract.
        $params = array_values($params);

        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$controller, $method] = $handler;

            if (is_string($controller)) {
                $controller = new $controller();
            }

            call_user_func_array([$controller, $method], $params);
        }
    }

    /**
     * Handle unmatched routes.
     *
     * Phase 6 SPA cutover catch-all: any non-API path that matches no route
     * falls through to the compiled Vue SPA shell (public/app/index.html) so
     * deep links (e.g. a hard refresh on /requests/42) boot the SPA and let
     * Vue Router resolve client-side. API misses stay JSON-shaped — an API
     * client must never receive HTML.
     */
    private static function notFound(): void
    {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '';

        // API misses → JSON 404 (never serve the HTML shell to an API client).
        // Substring (not str_starts_with) is intentional: under the
        // /hr_budget/public/ subdirectory deployment the miss URI is
        // /hr_budget/public/api/v1/... so a prefix check would wrongly serve HTML.
        if (str_contains($uri, '/api/')) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Not found']);
            return;
        }

        // Everything else → SPA shell (client-side routing takes over).
        $shell = BASE_PATH . '/public/app/index.html';
        if (is_file($shell)) {
            http_response_code(200);
            header('Content-Type: text/html; charset=UTF-8');
            readfile($shell);
            return;
        }

        // Build missing — fail visibly rather than serving a blank page.
        http_response_code(404);
        View::render('errors/404');
    }

    /**
     * Redirect to another URL
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        // Add base path for relative URLs
        if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
            $url = View::url($url);
        }
        header("Location: {$url}", true, $statusCode);
        exit;
    }

    /**
     * Generate URL for a path
     */
    public static function url(string $path): string
    {
        $baseUrl = rtrim($_ENV['APP_URL'] ?? '', '/');
        return $baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Get current route
     */
    public static function current(): array
    {
        return self::$currentRoute;
    }
}
