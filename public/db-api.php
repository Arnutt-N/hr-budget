<?php
/**
 * Database Inspection API
 * Secure read-only access to database for debugging
 * 
 * Usage: http://localhost/hr_budget/public/db-api.php?token=SECRET&query=SELECT * FROM budget_category_items LIMIT 5
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;

// Security: Only allow localhost access
$allowedHosts = ['localhost', '127.0.0.1', '::1'];
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', $allowedHosts) && 
    !in_array($_SERVER['HTTP_HOST'] ?? '', $allowedHosts)) {
    http_response_code(403);
    die(json_encode(['error' => 'Access denied: localhost only']));
}

// Security token (change this to a random string in production)
const API_TOKEN = 'debug_2024_hr_budget_secure';

header('Content-Type: application/json');

// Verify token
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== API_TOKEN) {
    http_response_code(401);
    die(json_encode(['error' => 'Invalid token']));
}

// Get action
$action = $_GET['action'] ?? 'query';

try {
    $db = Database::getPdo();
    
    switch ($action) {
        case 'tables':
            // List all tables
            $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            echo json_encode([
                'success' => true,
                'tables' => $tables,
                'count' => count($tables)
            ]);
            break;
            
        case 'describe':
            // Describe table structure
            $table = $_GET['table'] ?? '';
            if (empty($table)) {
                throw new Exception('Table name required');
            }
            
            // Sanitize table name
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            
            $columns = $db->query("DESCRIBE `$table`")->fetchAll(PDO::FETCH_ASSOC);
            $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            
            echo json_encode([
                'success' => true,
                'table' => $table,
                'columns' => $columns,
                'row_count' => $count
            ]);
            break;
            
        case 'query':
            // Execute SELECT query only
            $query = $_GET['query'] ?? $_POST['query'] ?? '';
            
            if (empty($query)) {
                throw new Exception('Query required');
            }
            
            // Security: Only allow SELECT queries
            $queryUpper = strtoupper(trim($query));
            if (!str_starts_with($queryUpper, 'SELECT') && 
                !str_starts_with($queryUpper, 'SHOW') && 
                !str_starts_with($queryUpper, 'DESCRIBE')) {
                throw new Exception('Only SELECT, SHOW, and DESCRIBE queries allowed');
            }
            
            // Prevent deletion/modification keywords
            $dangerousKeywords = ['DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'TRUNCATE', 'CREATE'];
            foreach ($dangerousKeywords as $keyword) {
                if (stripos($query, $keyword) !== false) {
                    throw new Exception("Dangerous keyword detected: $keyword");
                }
            }
            
            $stmt = $db->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'query' => $query,
                'row_count' => count($results),
                'data' => $results
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'quick':
            // Quick preset queries
            $preset = $_GET['preset'] ?? '';
            
            $queries = [
                'categories' => "SELECT * FROM budget_categories",
                'category_items' => "SELECT * FROM budget_category_items ORDER BY sort_order LIMIT 20",
                'requests' => "SELECT id, request_title, request_status, fiscal_year, created_at FROM budget_requests ORDER BY id DESC LIMIT 10",
                'request_items_5' => "SELECT * FROM budget_request_items WHERE budget_request_id = 5",
                'tables' => "SHOW TABLES",
            ];
            
            if (!isset($queries[$preset])) {
                throw new Exception("Unknown preset: $preset. Available: " . implode(', ', array_keys($queries)));
            }
            
            $stmt = $db->query($queries[$preset]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'preset' => $preset,
                'query' => $queries[$preset],
                'row_count' => count($results),
                'data' => $results
            ], JSON_PRETTY_PRINT);
            break;
            
        default:
            throw new Exception("Unknown action: $action");
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
