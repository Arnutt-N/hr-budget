---
name: monitoring_alerting
description: Guide for application monitoring, logging, and alerting in the HR Budget project.
---

# Monitoring & Alerting Guide

Standards for monitoring application health, performance, and sending alerts.

## 📑 Table of Contents

- [Health Checks](#-health-checks)
- [Application Metrics](#-application-metrics)
- [Log Monitoring](#-log-monitoring)
- [Alerting Channels](#-alerting-channels)
- [Dashboard Setup](#-dashboard-setup)

## 🏥 Health Checks

### Health Check Endpoint

```php
// src/Controllers/HealthController.php
class HealthController
{
    public function check(): void
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];
        
        $healthy = !in_array(false, array_column($checks, 'status'));
        
        http_response_code($healthy ? 200 : 503);
        header('Content-Type: application/json');
        
        echo json_encode([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'timestamp' => date('c'),
            'checks' => $checks
        ]);
    }
    
    private function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            Database::query("SELECT 1")->fetch();
            $latency = (microtime(true) - $start) * 1000;
            
            return [
                'status' => true,
                'latency_ms' => round($latency, 2),
                'message' => 'Connected'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }
    
    private function checkStorage(): array
    {
        $path = __DIR__ . '/../../storage';
        $writable = is_writable($path);
        $freeSpace = disk_free_space($path);
        $freeSpaceGB = round($freeSpace / 1024 / 1024 / 1024, 2);
        
        return [
            'status' => $writable && $freeSpaceGB > 1,
            'writable' => $writable,
            'free_space_gb' => $freeSpaceGB
        ];
    }
    
    private function checkCache(): array
    {
        try {
            $cache = new FileCache();
            $cache->set('health_check', 'ok', 60);
            $value = $cache->get('health_check');
            
            return [
                'status' => $value === 'ok',
                'message' => 'Cache working'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    private function checkQueue(): array
    {
        // Check pending queue items
        $pending = Database::query(
            "SELECT COUNT(*) FROM email_queue WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 1 HOUR)"
        )->fetchColumn();
        
        return [
            'status' => $pending < 100,
            'stale_jobs' => (int)$pending,
            'message' => $pending < 100 ? 'Queue healthy' : 'Queue backlog detected'
        ];
    }
}

// Route: GET /health
```

### Liveness & Readiness Probes

```php
// Kubernetes-style probes

// GET /health/live - Is the app running?
public function liveness(): void
{
    http_response_code(200);
    echo json_encode(['status' => 'alive']);
}

// GET /health/ready - Is the app ready to serve traffic?
public function readiness(): void
{
    $dbOk = $this->checkDatabase()['status'];
    $cacheOk = $this->checkCache()['status'];
    
    if ($dbOk && $cacheOk) {
        http_response_code(200);
        echo json_encode(['status' => 'ready']);
    } else {
        http_response_code(503);
        echo json_encode(['status' => 'not_ready']);
    }
}
```

## 📊 Application Metrics

### Metrics Collector

```php
class Metrics
{
    private static array $counters = [];
    private static array $gauges = [];
    private static array $histograms = [];
    
    public static function increment(string $name, array $labels = []): void
    {
        $key = self::buildKey($name, $labels);
        self::$counters[$key] = (self::$counters[$key] ?? 0) + 1;
    }
    
    public static function gauge(string $name, float $value, array $labels = []): void
    {
        $key = self::buildKey($name, $labels);
        self::$gauges[$key] = $value;
    }
    
    public static function histogram(string $name, float $value, array $labels = []): void
    {
        $key = self::buildKey($name, $labels);
        self::$histograms[$key][] = $value;
    }
    
    public static function export(): string
    {
        $output = "";
        
        // Counters
        foreach (self::$counters as $key => $value) {
            $output .= "{$key} {$value}\n";
        }
        
        // Gauges
        foreach (self::$gauges as $key => $value) {
            $output .= "{$key} {$value}\n";
        }
        
        // Histograms (simplified)
        foreach (self::$histograms as $key => $values) {
            $count = count($values);
            $sum = array_sum($values);
            $output .= "{$key}_count {$count}\n";
            $output .= "{$key}_sum {$sum}\n";
        }
        
        return $output;
    }
    
    private static function buildKey(string $name, array $labels): string
    {
        if (empty($labels)) return $name;
        
        $labelStr = [];
        foreach ($labels as $k => $v) {
            $labelStr[] = "{$k}=\"{$v}\"";
        }
        return $name . '{' . implode(',', $labelStr) . '}';
    }
}

// Usage
Metrics::increment('http_requests_total', ['method' => 'GET', 'path' => '/budgets']);
Metrics::histogram('request_duration_seconds', 0.245, ['path' => '/budgets']);
Metrics::gauge('active_users', 42);
```

### Prometheus Endpoint

```php
// GET /metrics
public function metrics(): void
{
    header('Content-Type: text/plain; charset=utf-8');
    
    // Collect current metrics
    Metrics::gauge('php_memory_usage_bytes', memory_get_usage());
    Metrics::gauge('php_memory_peak_bytes', memory_get_peak_usage());
    
    echo Metrics::export();
}
```

## 📋 Log Monitoring

### Structured Logging

```php
class StructuredLogger
{
    public static function log(string $level, string $message, array $context = []): void
    {
        $entry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'request_id' => $_SERVER['HTTP_X_REQUEST_ID'] ?? uniqid(),
            'user_id' => Auth::id(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ];
        
        $logFile = __DIR__ . '/../../logs/' . date('Y-m-d') . '.json';
        file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND);
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
    
    public static function critical(string $message, array $context = []): void
    {
        self::log('CRITICAL', $message, $context);
        
        // Auto-alert on critical
        AlertService::send('critical', $message, $context);
    }
}
```

### Log Analysis Patterns

```bash
# Count errors by type
cat logs/2025-01-15.json | jq -r 'select(.level=="ERROR") | .message' | sort | uniq -c | sort -rn

# Find slow queries (>1s)
cat logs/2025-01-15.json | jq 'select(.context.duration_ms > 1000)'

# Get unique error messages
cat logs/*.json | jq -r 'select(.level=="ERROR") | .message' | sort -u
```

## 🔔 Alerting Channels

### Alert Service

```php
class AlertService
{
    public static function send(string $severity, string $message, array $context = []): void
    {
        $alert = [
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'timestamp' => date('c'),
            'environment' => env('APP_ENV', 'production'),
        ];
        
        // Route based on severity
        switch ($severity) {
            case 'critical':
                self::sendToLine($alert);
                self::sendToEmail($alert);
                break;
            case 'warning':
                self::sendToLine($alert);
                break;
            default:
                // Log only
        }
    }
    
    private static function sendToLine(array $alert): void
    {
        $token = env('LINE_NOTIFY_TOKEN');
        if (!$token) return;
        
        $message = "[{$alert['severity']}] {$alert['environment']}\n";
        $message .= $alert['message'] . "\n";
        $message .= "Time: " . $alert['timestamp'];
        
        $ch = curl_init('https://notify-api.line.me/api/notify');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['message' => $message],
            CURLOPT_RETURNTRANSFER => true,
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
    
    private static function sendToEmail(array $alert): void
    {
        $mail = new MailService();
        $mail->send(
            env('ALERT_EMAIL', 'admin@example.com'),
            "[ALERT] {$alert['severity']}: {$alert['message']}",
            View::renderToString('emails/alert', $alert)
        );
    }
}
```

### Slack Integration (Alternative)

```php
private static function sendToSlack(array $alert): void
{
    $webhook = env('SLACK_WEBHOOK_URL');
    if (!$webhook) return;
    
    $color = match($alert['severity']) {
        'critical' => '#dc2626',
        'warning' => '#f59e0b',
        default => '#3b82f6'
    };
    
    $payload = [
        'attachments' => [[
            'color' => $color,
            'title' => "[{$alert['severity']}] {$alert['message']}",
            'fields' => [
                ['title' => 'Environment', 'value' => $alert['environment'], 'short' => true],
                ['title' => 'Time', 'value' => $alert['timestamp'], 'short' => true],
            ],
            'footer' => 'HR Budget Monitoring'
        ]]
    ];
    
    $ch = curl_init($webhook);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
    ]);
    curl_exec($ch);
    curl_close($ch);
}
```

## 📈 Dashboard Setup

### Grafana Dashboard JSON

```json
{
    "title": "HR Budget Application",
    "panels": [
        {
            "title": "Request Rate",
            "type": "graph",
            "targets": [{
                "expr": "rate(http_requests_total[5m])"
            }]
        },
        {
            "title": "Response Time",
            "type": "graph",
            "targets": [{
                "expr": "histogram_quantile(0.95, request_duration_seconds)"
            }]
        },
        {
            "title": "Error Rate",
            "type": "stat",
            "targets": [{
                "expr": "rate(http_requests_total{status=~'5..'}[5m])"
            }]
        }
    ]
}
```

### Simple Status Page

```php
// resources/views/status.php
<!DOCTYPE html>
<html>
<head>
    <title>System Status</title>
    <meta http-equiv="refresh" content="60">
    <style>
        .status-up { color: #22c55e; }
        .status-down { color: #ef4444; }
    </style>
</head>
<body>
    <h1>System Status</h1>
    <table>
        <?php foreach ($checks as $name => $check): ?>
        <tr>
            <td><?= htmlspecialchars($name) ?></td>
            <td class="<?= $check['status'] ? 'status-up' : 'status-down' ?>">
                <?= $check['status'] ? '✅ Operational' : '❌ Down' ?>
            </td>
            <td><?= $check['latency_ms'] ?? '-' ?> ms</td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p>Last updated: <?= date('Y-m-d H:i:s') ?></p>
</body>
</html>
```
