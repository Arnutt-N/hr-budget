---
name: performance_caching
description: Guide for performance optimization and caching strategies in the HR Budget project.
---

# Performance & Caching Guide

Best practices for optimizing application performance and implementing caching.

## 📑 Table of Contents

- [PHP Optimization](#-php-optimization)
- [Database Optimization](#-database-optimization)
- [Caching Strategies](#-caching-strategies)
- [HTTP Caching](#-http-caching)
- [Frontend Performance](#-frontend-performance)

## ⚡ PHP Optimization

### OPcache Configuration

```ini
; php.ini
[opcache]
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0          ; Set to 1 in development
opcache.revalidate_freq=0
opcache.fast_shutdown=1
opcache.enable_cli=1
```

### Preloading (PHP 8.0+)

```php
// config/preload.php
<?php
// Preload frequently used classes
require_once __DIR__ . '/../src/Core/Database.php';
require_once __DIR__ . '/../src/Core/Router.php';
require_once __DIR__ . '/../src/Core/View.php';
require_once __DIR__ . '/../src/Models/User.php';
require_once __DIR__ . '/../src/Models/Budget.php';

// php.ini: opcache.preload=/path/to/config/preload.php
```

### Avoid Expensive Operations

```php
// ❌ Bad - Reading file on every request
$config = json_decode(file_get_contents('config.json'), true);

// ✅ Good - Cache configuration
class Config
{
    private static ?array $config = null;
    
    public static function get(string $key, $default = null)
    {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/../config/app.php';
        }
        return self::$config[$key] ?? $default;
    }
}
```

## 🗄️ Database Optimization

### Query Optimization

```php
// ❌ Bad - N+1 Query Problem
$budgets = Budget::all();
foreach ($budgets as $budget) {
    $items = BudgetItem::where('budget_id', $budget['id'])->get();
    // Each iteration = 1 query
}

// ✅ Good - Eager Loading
$budgets = Database::query("
    SELECT b.*, GROUP_CONCAT(bi.id) as item_ids
    FROM budgets b
    LEFT JOIN budget_items bi ON bi.budget_id = b.id
    GROUP BY b.id
")->fetchAll();
```

### Index Optimization

```sql
-- Analyze slow queries
EXPLAIN SELECT * FROM budget_requests WHERE status = 'pending' AND user_id = 5;

-- Add composite index
CREATE INDEX idx_requests_user_status ON budget_requests(user_id, status);

-- Check index usage
SHOW INDEX FROM budget_requests;
```

### Query Result Caching

```php
class QueryCache
{
    private static array $cache = [];
    
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $cacheKey = md5($key);
        
        if (isset(self::$cache[$cacheKey]) && self::$cache[$cacheKey]['expires'] > time()) {
            return self::$cache[$cacheKey]['data'];
        }
        
        $data = $callback();
        self::$cache[$cacheKey] = [
            'data' => $data,
            'expires' => time() + $ttl
        ];
        
        return $data;
    }
}

// Usage
$organizations = QueryCache::remember('organizations_list', 3600, function() {
    return Database::query("SELECT * FROM organizations WHERE is_active = 1")->fetchAll();
});
```

## 💾 Caching Strategies

### File-Based Cache

```php
class FileCache
{
    private string $cachePath;
    
    public function __construct()
    {
        $this->cachePath = __DIR__ . '/../../storage/cache/';
    }
    
    public function get(string $key)
    {
        $file = $this->cachePath . md5($key) . '.cache';
        
        if (!file_exists($file)) return null;
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            return null;
        }
        
        return $data['value'];
    }
    
    public function set(string $key, $value, int $ttl = 3600): void
    {
        $file = $this->cachePath . md5($key) . '.cache';
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        file_put_contents($file, serialize($data));
    }
    
    public function delete(string $key): void
    {
        $file = $this->cachePath . md5($key) . '.cache';
        if (file_exists($file)) unlink($file);
    }
    
    public function flush(): void
    {
        array_map('unlink', glob($this->cachePath . '*.cache'));
    }
}
```

### Redis Cache (Optional)

```php
class RedisCache
{
    private \Redis $redis;
    
    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect(
            env('REDIS_HOST', '127.0.0.1'),
            env('REDIS_PORT', 6379)
        );
        
        if ($password = env('REDIS_PASSWORD')) {
            $this->redis->auth($password);
        }
    }
    
    public function get(string $key)
    {
        $value = $this->redis->get($key);
        return $value ? unserialize($value) : null;
    }
    
    public function set(string $key, $value, int $ttl = 3600): void
    {
        $this->redis->setex($key, $ttl, serialize($value));
    }
    
    public function delete(string $key): void
    {
        $this->redis->del($key);
    }
    
    public function flush(): void
    {
        $this->redis->flushDB();
    }
}
```

### Cache Tags Pattern

```php
class TaggedCache
{
    public function tags(array $tags): self
    {
        $this->currentTags = $tags;
        return $this;
    }
    
    public function set(string $key, $value, int $ttl = 3600): void
    {
        // Store tagged keys for bulk invalidation
        foreach ($this->currentTags as $tag) {
            $tagKey = "tag:{$tag}";
            $keys = $this->get($tagKey) ?? [];
            $keys[] = $key;
            parent::set($tagKey, array_unique($keys), 86400);
        }
        
        parent::set($key, $value, $ttl);
    }
    
    public function flushTag(string $tag): void
    {
        $tagKey = "tag:{$tag}";
        $keys = $this->get($tagKey) ?? [];
        
        foreach ($keys as $key) {
            $this->delete($key);
        }
        
        $this->delete($tagKey);
    }
}

// Usage
$cache->tags(['budgets', 'reports'])->set('dashboard_stats', $stats);
$cache->flushTag('budgets'); // Invalidate all budget-related cache
```

## 🌐 HTTP Caching

### Cache Headers

```php
class CacheControl
{
    public static function private(int $maxAge = 3600): void
    {
        header("Cache-Control: private, max-age={$maxAge}");
    }
    
    public static function public(int $maxAge = 86400): void
    {
        header("Cache-Control: public, max-age={$maxAge}");
    }
    
    public static function noCache(): void
    {
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    public static function etag(string $content): void
    {
        $etag = md5($content);
        header("ETag: \"{$etag}\"");
        
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
            trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
            http_response_code(304);
            exit;
        }
    }
}

// Usage in controller
public function getDashboardStats(): void
{
    CacheControl::private(300); // Cache for 5 minutes
    
    $stats = $this->getStats();
    $this->json($stats);
}
```

### Static Asset Versioning

```php
// In View helper
function asset(string $path): string
{
    $file = __DIR__ . '/../../public/' . ltrim($path, '/');
    $version = file_exists($file) ? filemtime($file) : time();
    return "/assets/{$path}?v={$version}";
}

// Usage
<link rel="stylesheet" href="<?= asset('css/app.css') ?>">
<script src="<?= asset('js/app.js') ?>"></script>
```

## 🎨 Frontend Performance

### Lazy Loading Images

```html
<img src="placeholder.png" data-src="actual-image.jpg" loading="lazy" alt="...">

<script>
document.querySelectorAll('img[data-src]').forEach(img => {
    img.src = img.dataset.src;
});
</script>
```

### Debounce & Throttle

```javascript
// Debounce - Wait until user stops typing
function debounce(func, wait = 300) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Usage for search
searchInput.addEventListener('input', debounce(async (e) => {
    const results = await search(e.target.value);
    renderResults(results);
}, 300));

// Throttle - Execute at most once per interval
function throttle(func, limit = 100) {
    let inThrottle;
    return function(...args) {
        if (!inThrottle) {
            func.apply(this, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Usage for scroll
window.addEventListener('scroll', throttle(handleScroll, 100));
```

### Bundle Optimization

```javascript
// vite.config.js
export default {
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs', 'chart.js'],
                    utils: ['./src/js/utils.js', './src/js/helpers.js']
                }
            }
        },
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true
            }
        }
    }
};
```

## 📊 Performance Monitoring

### Simple Profiler

```php
class Profiler
{
    private static array $timers = [];
    private static array $memory = [];
    
    public static function start(string $label): void
    {
        self::$timers[$label] = microtime(true);
        self::$memory[$label] = memory_get_usage();
    }
    
    public static function end(string $label): array
    {
        return [
            'time_ms' => (microtime(true) - self::$timers[$label]) * 1000,
            'memory_kb' => (memory_get_usage() - self::$memory[$label]) / 1024
        ];
    }
    
    public static function report(): void
    {
        if (!config('app.debug')) return;
        
        echo '<div class="profiler" style="position:fixed;bottom:0;right:0;background:#1e1e1e;color:#fff;padding:10px;font-size:12px;">';
        foreach (self::$timers as $label => $start) {
            $result = self::end($label);
            echo "<div>{$label}: {$result['time_ms']}ms | {$result['memory_kb']}KB</div>";
        }
        echo '</div>';
    }
}

// Usage
Profiler::start('database');
$data = Budget::all();
$result = Profiler::end('database');
```
