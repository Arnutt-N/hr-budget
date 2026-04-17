---
name: debugging_troubleshooting
description: Guide for debugging, error handling, and troubleshooting common issues in the HR Budget project.
---

# Debugging & Troubleshooting Guide

Comprehensive guide for identifying and resolving issues in the HR Budget application.

## 📑 Table of Contents

- [Debug Tools](#-debug-tools)
- [Error Handling](#-error-handling)
- [Common Issues](#-common-issues)
- [Logging](#-logging)
- [Performance Debugging](#-performance-debugging)

## 🔧 Debug Tools

### PHP Debug Mode

```php
// config/app.php
return [
    'debug' => env('APP_DEBUG', false),
];

// Usage in code
if (config('app.debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}
```

### Debug Helper Functions

```php
// src/Helpers/DebugHelper.php
class Debug
{
    public static function dump(...$vars): void
    {
        if (!config('app.debug')) return;
        
        echo '<pre style="background:#1e1e1e;color:#d4d4d4;padding:15px;margin:10px;border-radius:5px;">';
        foreach ($vars as $var) {
            var_dump($var);
            echo "\n---\n";
        }
        echo '</pre>';
    }
    
    public static function dd(...$vars): void
    {
        self::dump(...$vars);
        die();
    }
    
    public static function log(string $message, array $context = []): void
    {
        $log = date('Y-m-d H:i:s') . " | $message | " . json_encode($context) . "\n";
        file_put_contents(__DIR__ . '/../../logs/debug.log', $log, FILE_APPEND);
    }
    
    public static function sqlLog(string $query, array $params = []): void
    {
        if (!config('app.debug')) return;
        
        $log = [
            'time' => date('Y-m-d H:i:s'),
            'query' => $query,
            'params' => $params
        ];
        file_put_contents(__DIR__ . '/../../logs/sql.log', json_encode($log) . "\n", FILE_APPEND);
    }
}
```

### Browser DevTools Tips

| Tool | Usage |
|:-----|:------|
| **Network Tab** | ตรวจสอบ API requests/responses |
| **Console** | ดู JavaScript errors |
| **Application > Cookies** | ตรวจสอบ session cookies |
| **Elements** | Inspect DOM และ styles |

## ⚠️ Error Handling

### Global Error Handler

```php
// public/index.php
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    // Log error
    error_log($e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    
    if (config('app.debug')) {
        // Show detailed error in development
        echo "<h1>Error</h1>";
        echo "<p><strong>{$e->getMessage()}</strong></p>";
        echo "<p>{$e->getFile()}:{$e->getLine()}</p>";
        echo "<pre>{$e->getTraceAsString()}</pre>";
    } else {
        // Show user-friendly error in production
        http_response_code(500);
        include __DIR__ . '/../resources/views/errors/500.php';
    }
});
```

### Try-Catch Patterns

```php
// Database operations
try {
    $db->beginTransaction();
    
    Budget::create($data);
    BudgetItem::createMany($items);
    
    $db->commit();
} catch (PDOException $e) {
    $db->rollBack();
    
    if (str_contains($e->getMessage(), 'Duplicate entry')) {
        throw new ValidationException('ข้อมูลซ้ำในระบบ');
    }
    
    throw new DatabaseException('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
}

// API calls
try {
    $response = $httpClient->get($url);
} catch (ConnectionException $e) {
    Debug::log('API Connection failed', ['url' => $url, 'error' => $e->getMessage()]);
    throw new ServiceUnavailableException('ไม่สามารถเชื่อมต่อระบบภายนอกได้');
}
```

## 🔍 Common Issues

### 1. Session Issues

| Problem | Solution |
|:--------|:---------|
| Session not persisting | ตรวจสอบ `session_start()` ถูกเรียกก่อน output |
| Session expired too fast | เพิ่ม `session.gc_maxlifetime` |
| Cross-domain session | ตั้งค่า `session.cookie_domain` |

```php
// Debug session
Debug::dump($_SESSION, session_id(), session_status());
```

### 2. Database Connection

```php
// Test connection
try {
    $db = Database::getInstance();
    echo "✅ Connected to: " . $db->query("SELECT DATABASE()")->fetchColumn();
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}

// Check .env
Debug::dump([
    'DB_HOST' => env('DB_HOST'),
    'DB_NAME' => env('DB_DATABASE'),
    'DB_USER' => env('DB_USERNAME'),
    // Never dump password!
]);
```

### 3. File Upload Issues

| Problem | Check |
|:--------|:------|
| Upload fails silently | `upload_max_filesize`, `post_max_size` in php.ini |
| Permission denied | Directory permissions (755/775) |
| File not found | Path การสร้าง directory |

```php
// Debug upload
Debug::dump([
    'FILES' => $_FILES,
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir'),
]);
```

### 4. CSRF Token Mismatch

```php
// Verify CSRF is being sent
Debug::dump([
    'session_token' => $_SESSION['csrf_token'] ?? 'NOT SET',
    'post_token' => $_POST['csrf_token'] ?? 'NOT SENT',
    'meta_token' => 'Check <meta name="csrf-token"> in HTML'
]);
```

### 5. Route Not Found

```php
// Debug routing
Debug::dump([
    'REQUEST_URI' => $_SERVER['REQUEST_URI'],
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'],
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'],
    'registered_routes' => Router::getRoutes() // If available
]);
```

## 📋 Logging

### Log Levels

```php
class Logger
{
    public static function debug(string $message, array $context = []): void
    {
        self::log('DEBUG', $message, $context);
    }
    
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }
    
    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }
    
    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }
    
    private static function log(string $level, string $message, array $context): void
    {
        $logFile = __DIR__ . "/../../logs/" . date('Y-m-d') . ".log";
        $entry = sprintf(
            "[%s] %s: %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );
        file_put_contents($logFile, $entry, FILE_APPEND);
    }
}
```

### Log Rotation

```bash
# Cron job to clean old logs (keep 30 days)
find /var/www/hr_budget/logs -name "*.log" -mtime +30 -delete
```

## ⚡ Performance Debugging

### Query Profiling

```php
// Enable MySQL query log
$db->exec("SET profiling = 1");

// Run queries...
$result = Budget::getAll();

// Get profile
$profile = $db->query("SHOW PROFILES")->fetchAll();
Debug::dump($profile);
```

### Memory Usage

```php
// Track memory
$start = memory_get_usage();
// ... code ...
$end = memory_get_usage();

Debug::dump([
    'memory_used' => ($end - $start) / 1024 / 1024 . ' MB',
    'peak_memory' => memory_get_peak_usage() / 1024 / 1024 . ' MB'
]);
```

### Execution Time

```php
$startTime = microtime(true);

// ... code to profile ...

$duration = microtime(true) - $startTime;
Debug::log('Execution time', ['duration_ms' => $duration * 1000]);
```
