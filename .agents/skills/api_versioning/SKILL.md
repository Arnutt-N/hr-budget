---
name: api_versioning
description: Guide for API versioning and deprecation management in the HR Budget project.
---

# API Versioning & Deprecation Guide

Standards for managing API versions and graceful deprecation.

## 📑 Table of Contents

- [Versioning Strategies](#-versioning-strategies)
- [Implementation](#-implementation)
- [Deprecation Process](#-deprecation-process)
- [Changelog Management](#-changelog-management)

## 📋 Versioning Strategies

### Recommended: URL Path Versioning

```
/api/v1/budgets     # Version 1
/api/v2/budgets     # Version 2 (breaking changes)
```

### Route Structure

```php
// Version 1 routes
Router::group(['prefix' => '/api/v1'], function() {
    Router::get('/budgets', [V1\BudgetController::class, 'index']);
    Router::post('/budgets', [V1\BudgetController::class, 'store']);
});

// Version 2 routes
Router::group(['prefix' => '/api/v2'], function() {
    Router::get('/budgets', [V2\BudgetController::class, 'index']);
    Router::get('/budgets/{id}/analytics', [V2\BudgetController::class, 'analytics']);
});
```

## 🛠️ Implementation

### Controller Organization

```
src/Controllers/Api/
├── V1/
│   └── BudgetController.php
└── V2/
    └── BudgetController.php
```

### Response Transformers

```php
// V1 Transformer
class V1BudgetTransformer
{
    public function transform(array $budget): array
    {
        return [
            'id' => $budget['id'],
            'name' => $budget['name'],
            'amount' => $budget['total_amount'],
        ];
    }
}

// V2 Transformer (new fields)
class V2BudgetTransformer
{
    public function transform(array $budget): array
    {
        return [
            'id' => $budget['id'],
            'name' => $budget['name'],
            'total_amount' => (float)$budget['total_amount'],
            'spent_amount' => (float)$budget['spent_amount'],
            'remaining' => $budget['total_amount'] - $budget['spent_amount'],
            '_links' => [
                'self' => "/api/v2/budgets/{$budget['id']}",
            ],
        ];
    }
}
```

## ⚠️ Deprecation Process

### Deprecation Headers

```php
class DeprecationMiddleware
{
    private const DEPRECATED = [
        'v1' => '2025-06-01', // Sunset date
    ];
    
    public function handle(): void
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        foreach (self::DEPRECATED as $version => $sunsetDate) {
            if (str_contains($uri, "/api/{$version}")) {
                header('Deprecation: true');
                header("Sunset: {$sunsetDate}");
                header("X-Deprecation-Notice: Migrate to v2 before {$sunsetDate}");
            }
        }
    }
}
```

### Sunset Response (After deadline)

```php
if ($version === '1' && date('Y-m-d') > '2025-06-01') {
    http_response_code(410);
    echo json_encode([
        'error' => 'API Version Sunset',
        'message' => 'API v1 discontinued. Please upgrade to v2.',
        'migration_guide' => 'https://docs.example.com/api/migration'
    ]);
}
```

## 📝 Changelog Management

### Changelog Format

```markdown
## v2.0.0 (2025-01-15) - Breaking Changes

### Breaking Changes
- Response structure: `{ data: {...}, meta: {...} }`
- `amount` renamed to `total_amount`

### Added
- `spent_amount` and `remaining` fields
- Analytics endpoint

### Migration Guide
See /docs/migration-v1-v2.md
```

### Changelog Endpoint

```php
// GET /api/changelog
public function changelog(): void
{
    $this->json([
        'versions' => [
            ['version' => 'v2.0.0', 'status' => 'current', 'release_date' => '2025-01-15'],
            ['version' => 'v1.0.0', 'status' => 'deprecated', 'sunset' => '2025-06-01']
        ],
        'migration_guides' => [
            'v1_to_v2' => '/api/docs/migration/v1-v2'
        ]
    ]);
}
```

## 🔀 Version Negotiation

### Content Negotiation

```php
class VersionNegotiator
{
    public static function negotiate(): string
    {
        // 1. Check URL path first (highest priority)
        if (preg_match('/\/api\/v(\d+)/', $_SERVER['REQUEST_URI'], $matches)) {
            return $matches[1];
        }
        
        // 2. Check custom header
        if (isset($_SERVER['HTTP_X_API_VERSION'])) {
            return $_SERVER['HTTP_X_API_VERSION'];
        }
        
        // 3. Check Accept header (e.g., "application/vnd.hrbudget.v2+json")
        if (isset($_SERVER['HTTP_ACCEPT']) && 
            preg_match('/application\/vnd\.hrbudget\.v(\d+)\+json/', $_SERVER['HTTP_ACCEPT'], $matches)) {
            return $matches[1];
        }
        
        // 4. Default to latest stable
        return '2';
    }
}

// Usage
$version = VersionNegotiator::negotiate();
$controller = "App\\Controllers\\Api\\V{$version}\\BudgetController";
```

### Version-Aware Base Controller

```php
abstract class BaseApiController
{
    protected string $version;
    protected string $requestedVersion;
    
    public function __construct()
    {
        $this->requestedVersion = VersionNegotiator::negotiate();
        $this->version = $this->resolveVersion($this->requestedVersion);
        
        // Set version header in response
        header("X-API-Version: {$this->version}");
    }
    
    private function resolveVersion(string $requested): string
    {
        // Map requested to available
        $available = ['1', '2'];
        
        if (in_array($requested, $available)) {
            return $requested;
        }
        
        // Return latest if invalid
        return end($available);
    }
    
    protected function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        
        // Version-specific response wrapping
        if ($this->version === '2') {
            $response = [
                'data' => $data,
                'meta' => [
                    'version' => $this->version,
                    'timestamp' => date('c')
                ]
            ];
        } else {
            $response = $data; // v1 format
        }
        
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    }
}
```

## 🧪 Backward Compatibility Testing

### Compatibility Test Suite

```php
class ApiCompatibilityTest
{
    public function testV1ResponseStructure(): void
    {
        $response = $this->getJson('/api/v1/budgets/1');
        
        // Assert v1 structure
        $this->assertArrayHasKey('id', $response);
        $this->assertArrayHasKey('name', $response);
        $this->assertArrayHasKey('amount', $response); // v1 field name
        $this->assertArrayNotHasKey('total_amount', $response); // v2 field
    }
    
    public function testV2ResponseStructure(): void
    {
        $response = $this->getJson('/api/v2/budgets/1');
        
        // Assert v2 wrapper
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('meta', $response);
        
        // Assert v2 data structure
        $this->assertArrayHasKey('total_amount', $response['data']);
        $this->assertArrayHasKey('spent_amount', $response['data']);
        $this->assertArrayHasKey('_links', $response['data']);
    }
    
    public function testDeprecationHeaders(): void
    {
        $response = $this->get('/api/v1/budgets');
        
        $this->assertEquals('true', $response->headers->get('Deprecation'));
        $this->assertNotNull($response->headers->get('Sunset'));
        $this->assertNotNull($response->headers->get('X-Deprecation-Notice'));
    }
}
```

### Contract Testing

```yaml
# api-contract-v1.yaml
request:
  path: /api/v1/budgets/{id}
  method: GET

response:
  status: 200
  schema:
    type: object
    required: [id, name, amount]
    properties:
      id: { type: integer }
      name: { type: string }
      amount: { type: number }
```

## 🛠️ Migration Tools

### Automated Migration Script

```php
class ApiMigrationHelper
{
    public static function convertV1RequestToV2(array $v1Request): array
    {
        $v2Request = $v1Request;
        
        // Map renamed fields
        if (isset($v1Request['amount'])) {
            $v2Request['total_amount'] = $v1Request['amount'];
            unset($v2Request['amount']);
        }
        
        return $v2Request;
    }
    
    public static function convertV2ResponseToV1(array $v2Response): array
    {
        // Extract from wrapper if present
        $data = $v2Response['data'] ?? $v2Response;
        
        // Map v2 fields to v1
        $v1Response = [
            'id' => $data['id'],
            'name' => $data['name'],
            'amount' => $data['total_amount'] ?? 0,
        ];
        
        // Remove v2-only fields
        unset($v1Response['spent_amount'], $v1Response['remaining'], $v1Response['_links']);
        
        return $v1Response;
    }
    
    public static function proxyV1ToV2(string $endpoint, array $data): array
    {
        // Convert request
        $v2Data = self::convertV1RequestToV2($data);
        
        // Call v2 endpoint
        $v2Endpoint = str_replace('/api/v1/', '/api/v2/', $endpoint);
        $v2Response = Http::post($v2Endpoint, $v2Data);
        
        // Convert response back to v1 format
        return self::convertV2ResponseToV1($v2Response);
    }
}
```

### Migration Guide Generator

```php
class MigrationGuideGenerator
{
    public static function generate(string $fromVersion, string $toVersion): string
    {
        $changes = self::getDiff($fromVersion, $toVersion);
        
        $guide = "# Migration Guide: v{$fromVersion} to v{$toVersion}\n\n";
        
        // Breaking changes
        if (!empty($changes['breaking'])) {
            $guide .= "## ⚠️ Breaking Changes\n\n";
            foreach ($changes['breaking'] as $change) {
                $guide .= "- **{$change['field']}**: {$change['description']}\n";
                $guide .= "  ```php\n  // Before (v{$fromVersion})\n  {$change['before']}\n\n";
                $guide .= "  // After (v{$toVersion})\n  {$change['after']}\n  ```\n\n";
            }
        }
        
        // New features
        if (!empty($changes['added'])) {
            $guide .= "## ✨ New Features\n\n";
            foreach ($changes['added'] as $feature) {
                $guide .= "- {$feature}\n";
            }
        }
        
        return $guide;
    }
}
```

## 📊 Version Usage Tracking

### Analytics

```php
class ApiVersionAnalytics
{
    public static function track(string $version, string $endpoint, ?int $userId = null): void
    {
        Database::insert('api_usage_logs', [
            'version' => $version,
            'endpoint' => $endpoint,
            'user_id' => $userId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public static function getVersionDistribution(): array
    {
        return Database::query("
            SELECT version, COUNT(*) as count
            FROM api_usage_logs
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY version
        ")->fetchAll();
    }
    
    public static function getUsersOnOldVersion(): array
    {
        return Database::query("
            SELECT DISTINCT user_id, COUNT(*) as request_count
            FROM api_usage_logs
            WHERE version = '1' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            GROUP BY user_id
            ORDER BY request_count DESC
        ")->fetchAll();
    }
}

// Alert if too many users still on v1
$v1Users = ApiVersionAnalytics::getUsersOnOldVersion();
if (count($v1Users) > 10) {
    AlertService::send('warning', "Still {count($v1Users)} users on API v1");
}
```

## 📧 Client Communication

### Automated Deprecation Emails

```php
class DeprecationNotifier
{
    public static function notifyV1Users(): void
    {
        $users = ApiVersionAnalytics::getUsersOnOldVersion();
        
        foreach ($users as $user) {
            $userData = User::find($user['user_id']);
            
            $mail = new MailService();
            $mail->send(
                $userData['email'],
                'การแจ้งเตือน: API v1 จะถูกยกเลิกเร็วๆ นี้',
                View::renderToString('emails/api_v1_deprecation', [
                    'user' => $userData,
                    'sunset_date' => '2025-06-01',
                    'migration_guide' => url('/api/docs/migration/v1-v2')
                ])
            );
        }
    }
}

// Run weekly via cron
// 0 9 * * 1 php scripts/notify_api_deprecation.php
```

