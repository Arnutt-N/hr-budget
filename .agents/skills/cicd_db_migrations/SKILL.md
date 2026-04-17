---
name: cicd_db_migrations
description: Guide for CI/CD database migrations and automated schema updates.
---

# CI/CD Database Migrations Guide

Automating database schema changes in deployment pipelines.

## 📑 Table of Contents

- [Migration Strategy](#-migration-strategy)
- [Migration Files](#-migration-files)
- [CI/CD Integration](#-cicd-integration)
- [Rollback Procedures](#-rollback-procedures)

## 📋 Migration Strategy

### Migration Naming Convention

```
YYYYMMDD_HHMMSS_description.sql

Examples:
20250115_120000_create_audit_logs_table.sql
20250115_130000_add_status_to_budgets.sql
20250116_090000_drop_legacy_columns.sql
```

### Migrations Table

```sql
CREATE TABLE migrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    migration VARCHAR(255) NOT NULL UNIQUE,
    batch INT NOT NULL,
    executed_at DATETIME NOT NULL
);
```

## 📁 Migration Files

### Directory Structure

```
database/
├── migrations/
│   ├── 20250115_120000_create_audit_logs.sql
│   └── 20250115_130000_add_indexes.sql
└── seeds/
    └── initial_data.sql
```

### Migration Format

```sql
-- Up: Apply changes
CREATE TABLE audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    action VARCHAR(50) NOT NULL,
    created_at DATETIME NOT NULL
);

-- Down: Rollback changes
-- DROP TABLE audit_logs;
```

## 🔄 CI/CD Integration

### Migration Runner

```php
class Migrator
{
    public function run(): array
    {
        $migrations = glob(__DIR__ . '/../database/migrations/*.sql');
        $executed = $this->getExecuted();
        $batch = $this->getNextBatch();
        $ran = [];
        
        foreach ($migrations as $file) {
            $name = basename($file);
            if (!in_array($name, $executed)) {
                $this->runMigration($file, $name, $batch);
                $ran[] = $name;
            }
        }
        
        return $ran;
    }
    
    private function runMigration(string $file, string $name, int $batch): void
    {
        $sql = file_get_contents($file);
        
        // Extract UP portion (before -- Down:)
        $upSql = preg_split('/--\s*Down:/i', $sql)[0];
        
        Database::exec($upSql);
        
        Database::insert('migrations', [
            'migration' => $name,
            'batch' => $batch,
            'executed_at' => date('Y-m-d H:i:s')
        ]);
    }
}
```

### GitHub Actions Workflow

```yaml
name: Deploy with Migrations

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Run Migrations
        run: |
          php scripts/migrate.php
        env:
          DB_HOST: ${{ secrets.DB_HOST }}
          DB_DATABASE: ${{ secrets.DB_DATABASE }}
          DB_USERNAME: ${{ secrets.DB_USERNAME }}
          DB_PASSWORD: ${{ secrets.DB_PASSWORD }}
      
      - name: Deploy Application
        run: |
          rsync -avz --delete ./ user@server:/var/www/app/
```

### Pre-deployment Check

```php
// scripts/migrate.php
try {
    $migrator = new Migrator();
    
    // Backup before migration
    exec('mysqldump -u root -p dbname > backup_pre_migration.sql');
    
    $ran = $migrator->run();
    
    if (empty($ran)) {
        echo "No new migrations.\n";
    } else {
        echo "Ran " . count($ran) . " migrations:\n";
        foreach ($ran as $m) echo "  - {$m}\n";
    }
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
```

## ⏪ Rollback Procedures

### Rollback Last Batch

```php
public function rollback(): array
{
    $lastBatch = Database::query(
        "SELECT MAX(batch) FROM migrations"
    )->fetchColumn();
    
    $migrations = Database::query(
        "SELECT * FROM migrations WHERE batch = ? ORDER BY id DESC",
        [$lastBatch]
    )->fetchAll();
    
    $rolledBack = [];
    
    foreach ($migrations as $m) {
        $file = __DIR__ . "/../database/migrations/{$m['migration']}";
        $sql = file_get_contents($file);
        
        // Extract DOWN portion
        if (preg_match('/--\s*Down:\s*(.+)$/is', $sql, $matches)) {
            Database::exec($matches[1]);
        }
        
        Database::delete('migrations', $m['id']);
        $rolledBack[] = $m['migration'];
    }
    
    return $rolledBack;
}
```

### Emergency Rollback

```bash
# Restore from backup
mysql -u root -p dbname < backup_pre_migration.sql

# Clear failed migrations from table
mysql -u root -p dbname -e "DELETE FROM migrations WHERE batch = (SELECT MAX(batch) FROM migrations)"
```
