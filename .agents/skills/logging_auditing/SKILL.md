---
name: logging_auditing
description: Guide for activity logging and audit trail in the HR Budget project.
---

# Logging & Auditing Guide

Standards for tracking user actions and maintaining audit trails.

## 📑 Table of Contents

- [Audit Log Schema](#-audit-log-schema)
- [Audit Logger Class](#-audit-logger-class)
- [Automatic Logging](#-automatic-logging)
- [Viewing Audit Logs](#-viewing-audit-logs)

## 🗄️ Audit Log Schema

```sql
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    model_type VARCHAR(100) NOT NULL,
    model_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME NOT NULL,
    INDEX idx_user (user_id),
    INDEX idx_model (model_type, model_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

## 📝 Audit Logger Class

```php
class AuditLogger
{
    public static function log(
        string $action,
        string $modelType,
        ?int $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        Database::insert('audit_logs', [
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public static function created(string $model, int $id, array $data): void
    {
        self::log('created', $model, $id, null, self::sanitize($data));
    }
    
    public static function updated(string $model, int $id, array $old, array $new): void
    {
        $changes = self::getChanges($old, $new);
        if (!empty($changes['old']) || !empty($changes['new'])) {
            self::log('updated', $model, $id, $changes['old'], $changes['new']);
        }
    }
    
    public static function deleted(string $model, int $id, array $data): void
    {
        self::log('deleted', $model, $id, self::sanitize($data), null);
    }
    
    private static function sanitize(array $data): array
    {
        // Remove sensitive fields
        unset($data['password'], $data['two_factor_secret']);
        return $data;
    }
    
    private static function getChanges(array $old, array $new): array
    {
        $oldChanges = [];
        $newChanges = [];
        
        foreach ($new as $key => $value) {
            if (isset($old[$key]) && $old[$key] !== $value) {
                $oldChanges[$key] = $old[$key];
                $newChanges[$key] = $value;
            }
        }
        
        return ['old' => $oldChanges, 'new' => $newChanges];
    }
}
```

## 🔄 Automatic Logging

### Model Trait

```php
trait Auditable
{
    public static function createWithAudit(array $data): int
    {
        $id = parent::create($data);
        AuditLogger::created(static::class, $id, $data);
        return $id;
    }
    
    public static function updateWithAudit(int $id, array $data): bool
    {
        $old = static::find($id);
        $result = parent::update($id, $data);
        AuditLogger::updated(static::class, $id, $old, $data);
        return $result;
    }
    
    public static function deleteWithAudit(int $id): bool
    {
        $old = static::find($id);
        $result = parent::delete($id);
        AuditLogger::deleted(static::class, $id, $old);
        return $result;
    }
}

// Usage
class Budget extends Model
{
    use Auditable;
}
```

## 👀 Viewing Audit Logs

### Query Examples

```php
// Get activity for a specific record
$logs = AuditLogger::getForModel('Budget', 123);

// Get recent activity by user
$logs = AuditLogger::getByUser($userId, 50);

// Get activity in date range
$logs = Database::query(
    "SELECT al.*, u.name as user_name 
     FROM audit_logs al
     LEFT JOIN users u ON al.user_id = u.id
     WHERE al.created_at BETWEEN ? AND ?
     ORDER BY al.created_at DESC",
    [$startDate, $endDate]
)->fetchAll();
```

### Retention Policy

```bash
# Cron: Delete logs older than 1 year
DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```
