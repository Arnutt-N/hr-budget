---
name: api_rate_limiting
description: Guide for implementing API rate limiting and throttling in the HR Budget project.
---

# API Rate Limiting & Throttling Guide

Standards for protecting APIs from abuse and ensuring fair usage.

## 📑 Table of Contents

- [Rate Limiting Strategies](#-rate-limiting-strategies)
- [Implementation](#-implementation)
- [Response Headers](#-response-headers)
- [Bypass & Exceptions](#-bypass--exceptions)
- [Monitoring](#-monitoring)

## 📋 Rate Limiting Strategies

### Common Algorithms

| Algorithm | Description | Use Case |
|:----------|:------------|:---------|
| **Fixed Window** | Count requests per fixed time window | Simple APIs |
| **Sliding Window** | Rolling time window | More accurate limiting |
| **Token Bucket** | Refill tokens at fixed rate | Burst-friendly |
| **Leaky Bucket** | Process at fixed rate | Smooth traffic |

### Rate Limit Tiers

| Tier | Requests/Min | Requests/Hour | For |
|:-----|:------------:|:-------------:|:----|
| **Anonymous** | 30 | 500 | Unauthenticated users |
| **Basic** | 60 | 1,000 | Regular users |
| **Pro** | 120 | 5,000 | Power users |
| **Admin** | 300 | 10,000 | Administrators |

## 🛠️ Implementation

### Session-Based Rate Limiter

```php
class RateLimiter
{
    private string $key;
    private int $maxAttempts;
    private int $decaySeconds;
    
    public function __construct(string $key, int $maxAttempts = 60, int $decaySeconds = 60)
    {
        $this->key = $key;
        $this->maxAttempts = $maxAttempts;
        $this->decaySeconds = $decaySeconds;
    }
    
    public function tooManyAttempts(): bool
    {
        return $this->attempts() >= $this->maxAttempts;
    }
    
    public function hit(): int
    {
        $key = $this->getCacheKey();
        
        if (!isset($_SESSION['rate_limiter'][$key])) {
            $_SESSION['rate_limiter'][$key] = [
                'attempts' => 0,
                'reset_at' => time() + $this->decaySeconds
            ];
        }
        
        // Reset if expired
        if ($_SESSION['rate_limiter'][$key]['reset_at'] <= time()) {
            $_SESSION['rate_limiter'][$key] = [
                'attempts' => 0,
                'reset_at' => time() + $this->decaySeconds
            ];
        }
        
        $_SESSION['rate_limiter'][$key]['attempts']++;
        
        return $_SESSION['rate_limiter'][$key]['attempts'];
    }
    
    public function attempts(): int
    {
        $key = $this->getCacheKey();
        
        if (!isset($_SESSION['rate_limiter'][$key])) {
            return 0;
        }
        
        if ($_SESSION['rate_limiter'][$key]['reset_at'] <= time()) {
            return 0;
        }
        
        return $_SESSION['rate_limiter'][$key]['attempts'];
    }
    
    public function remaining(): int
    {
        return max(0, $this->maxAttempts - $this->attempts());
    }
    
    public function resetAt(): int
    {
        $key = $this->getCacheKey();
        return $_SESSION['rate_limiter'][$key]['reset_at'] ?? time() + $this->decaySeconds;
    }
    
    public function clear(): void
    {
        $key = $this->getCacheKey();
        unset($_SESSION['rate_limiter'][$key]);
    }
    
    private function getCacheKey(): string
    {
        return 'rate_limit:' . md5($this->key);
    }
}
```

### Redis-Based Rate Limiter (Recommended for Production)

```php
class RedisRateLimiter
{
    private \Redis $redis;
    
    public function __construct()
    {
        $this->redis = new \Redis();
        $this->redis->connect(env('REDIS_HOST', '127.0.0.1'), env('REDIS_PORT', 6379));
    }
    
    public function attempt(string $key, int $maxAttempts = 60, int $decaySeconds = 60): array
    {
        $cacheKey = "rate_limit:{$key}";
        
        $current = (int)$this->redis->get($cacheKey);
        
        if ($current >= $maxAttempts) {
            $ttl = $this->redis->ttl($cacheKey);
            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => $ttl
            ];
        }
        
        $pipe = $this->redis->multi(\Redis::PIPELINE);
        $pipe->incr($cacheKey);
        $pipe->expire($cacheKey, $decaySeconds);
        $pipe->exec();
        
        return [
            'allowed' => true,
            'remaining' => $maxAttempts - $current - 1,
            'retry_after' => 0
        ];
    }
    
    public function slidingWindow(string $key, int $maxAttempts, int $windowSeconds): array
    {
        $now = microtime(true);
        $windowStart = $now - $windowSeconds;
        $cacheKey = "rate_limit:{$key}";
        
        // Remove old entries
        $this->redis->zRemRangeByScore($cacheKey, '-inf', $windowStart);
        
        // Count current requests
        $count = $this->redis->zCard($cacheKey);
        
        if ($count >= $maxAttempts) {
            $oldestEntry = $this->redis->zRange($cacheKey, 0, 0, true);
            $retryAfter = reset($oldestEntry) + $windowSeconds - $now;
            
            return [
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => ceil($retryAfter)
            ];
        }
        
        // Add current request
        $this->redis->zAdd($cacheKey, $now, uniqid('', true));
        $this->redis->expire($cacheKey, $windowSeconds);
        
        return [
            'allowed' => true,
            'remaining' => $maxAttempts - $count - 1,
            'retry_after' => 0
        ];
    }
}
```

### Rate Limit Middleware

```php
class RateLimitMiddleware
{
    public function handle(): bool
    {
        // Determine key (IP or user ID)
        $key = Auth::check() 
            ? 'user:' . Auth::id() 
            : 'ip:' . $_SERVER['REMOTE_ADDR'];
        
        // Get limits based on user role
        $limits = $this->getLimits();
        
        $limiter = new RateLimiter($key, $limits['per_minute'], 60);
        $limiter->hit();
        
        // Set response headers
        $this->setHeaders($limiter, $limits['per_minute']);
        
        if ($limiter->tooManyAttempts()) {
            http_response_code(429);
            header('Retry-After: ' . ($limiter->resetAt() - time()));
            
            echo json_encode([
                'error' => 'Too many requests',
                'message' => 'คำขอมากเกินไป กรุณารอสักครู่',
                'retry_after' => $limiter->resetAt() - time()
            ]);
            
            return false;
        }
        
        return true;
    }
    
    private function getLimits(): array
    {
        if (!Auth::check()) {
            return ['per_minute' => 30, 'per_hour' => 500];
        }
        
        return match(Auth::role()) {
            'super_admin', 'admin' => ['per_minute' => 300, 'per_hour' => 10000],
            'director', 'division_head' => ['per_minute' => 120, 'per_hour' => 5000],
            default => ['per_minute' => 60, 'per_hour' => 1000],
        };
    }
    
    private function setHeaders(RateLimiter $limiter, int $maxAttempts): void
    {
        header("X-RateLimit-Limit: {$maxAttempts}");
        header("X-RateLimit-Remaining: {$limiter->remaining()}");
        header("X-RateLimit-Reset: {$limiter->resetAt()}");
    }
}
```

## 📨 Response Headers

### Standard Rate Limit Headers

```
HTTP/1.1 200 OK
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1705312800
```

### 429 Too Many Requests Response

```
HTTP/1.1 429 Too Many Requests
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1705312800
Retry-After: 45
Content-Type: application/json

{
    "error": "Too many requests",
    "message": "Rate limit exceeded. Please try again in 45 seconds.",
    "retry_after": 45
}
```

## 🔓 Bypass & Exceptions

### Whitelist Configuration

```php
class RateLimitConfig
{
    // Endpoints with custom limits
    public const ENDPOINT_LIMITS = [
        '/api/health' => ['per_minute' => 1000, 'per_hour' => 50000], // No limit
        '/api/login' => ['per_minute' => 5, 'per_hour' => 20],       // Strict
        '/api/reports/export' => ['per_minute' => 5, 'per_hour' => 50],
    ];
    
    // IPs to whitelist (internal services)
    public const WHITELIST_IPS = [
        '127.0.0.1',
        '10.0.0.0/8',  // Internal network
    ];
    
    public static function getEndpointLimits(string $path): ?array
    {
        return self::ENDPOINT_LIMITS[$path] ?? null;
    }
    
    public static function isWhitelisted(string $ip): bool
    {
        foreach (self::WHITELIST_IPS as $allowed) {
            if (str_contains($allowed, '/')) {
                // CIDR check
                if (self::ipInRange($ip, $allowed)) {
                    return true;
                }
            } elseif ($ip === $allowed) {
                return true;
            }
        }
        return false;
    }
    
    private static function ipInRange(string $ip, string $cidr): bool
    {
        [$subnet, $mask] = explode('/', $cidr);
        return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
    }
}
```

### API Key-Based Rate Limiting

```php
class ApiKeyRateLimiter
{
    public function handle(): bool
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;
        
        if (!$apiKey) {
            return $this->applyAnonymousLimit();
        }
        
        $client = ApiClient::findByKey($apiKey);
        
        if (!$client) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid API key']);
            return false;
        }
        
        // Apply client-specific limits
        $limiter = new RateLimiter(
            "api_key:{$client['id']}",
            $client['rate_limit'] ?? 60,
            60
        );
        
        $limiter->hit();
        
        if ($limiter->tooManyAttempts()) {
            http_response_code(429);
            echo json_encode(['error' => 'Rate limit exceeded for your API key']);
            return false;
        }
        
        return true;
    }
}
```

## 📊 Monitoring

### Rate Limit Metrics

```php
class RateLimitMetrics
{
    public static function recordHit(string $key, bool $limited): void
    {
        $metrics = $_SESSION['rate_limit_metrics'] ?? [];
        
        $today = date('Y-m-d');
        $hour = date('H');
        
        if (!isset($metrics[$today][$hour])) {
            $metrics[$today][$hour] = ['hits' => 0, 'limited' => 0];
        }
        
        $metrics[$today][$hour]['hits']++;
        if ($limited) {
            $metrics[$today][$hour]['limited']++;
        }
        
        $_SESSION['rate_limit_metrics'] = $metrics;
        
        // Log if too many users are being limited
        $limitedRatio = $metrics[$today][$hour]['limited'] / $metrics[$today][$hour]['hits'];
        if ($limitedRatio > 0.1) { // More than 10% being limited
            Logger::warning('High rate limit ratio', [
                'hour' => $hour,
                'ratio' => round($limitedRatio * 100, 2) . '%'
            ]);
        }
    }
    
    public static function getStats(): array
    {
        return $_SESSION['rate_limit_metrics'] ?? [];
    }
}
```

### Alert on Abuse

```php
class AbuseDetector
{
    public static function check(string $ip): void
    {
        $key = "abuse:{$ip}";
        $count = $_SESSION[$key]['count'] ?? 0;
        $firstHit = $_SESSION[$key]['first_hit'] ?? time();
        
        $_SESSION[$key] = [
            'count' => $count + 1,
            'first_hit' => $firstHit
        ];
        
        // More than 1000 requests in 10 minutes
        if ($count > 1000 && (time() - $firstHit) < 600) {
            AlertService::send('warning', "Potential API abuse from IP: {$ip}", [
                'requests' => $count,
                'duration_seconds' => time() - $firstHit
            ]);
        }
    }
}
```
