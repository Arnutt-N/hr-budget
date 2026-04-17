---
name: backup_disaster_recovery
description: Guide for database backup, file backup, and disaster recovery procedures.
---

# Backup & Disaster Recovery Guide

Standards for data protection and recovery procedures.

## 📑 Table of Contents

- [Backup Strategy](#-backup-strategy)
- [Database Backup](#-database-backup)
- [File Backup](#-file-backup)
- [Recovery Procedures](#-recovery-procedures)
- [Testing & Validation](#-testing--validation)

## 📋 Backup Strategy

### Backup Types

| Type | Frequency | Retention | Storage |
|:-----|:----------|:----------|:--------|
| **Full DB Backup** | Daily | 30 days | Remote + Local |
| **Incremental DB** | Every 6 hours | 7 days | Local |
| **File Uploads** | Daily | 30 days | Remote |
| **Config Files** | On change | Forever | Git |
| **Logs** | Daily | 90 days | Remote |

### 3-2-1 Rule

- **3** copies of data (1 primary + 2 backups)
- **2** different storage types (local disk + cloud)
- **1** offsite backup (cloud/remote server)

## 🗄️ Database Backup

### Full Backup Script

```bash
#!/bin/bash
# scripts/backup_db.sh

# Configuration
DB_HOST="${DB_HOST:-localhost}"
DB_NAME="${DB_NAME:-hr_budget}"
DB_USER="${DB_USER:-root}"
DB_PASS="${DB_PASSWORD}"
BACKUP_DIR="/var/backups/hr_budget/db"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=30

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Create backup
BACKUP_FILE="$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"

mysqldump \
    --host="$DB_HOST" \
    --user="$DB_USER" \
    --password="$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-table \
    "$DB_NAME" | gzip > "$BACKUP_FILE"

# Verify backup
if [ -s "$BACKUP_FILE" ]; then
    echo "[$(date)] Backup created: $BACKUP_FILE ($(stat -f%z "$BACKUP_FILE") bytes)"
    
    # Upload to remote (optional)
    # aws s3 cp "$BACKUP_FILE" "s3://hr-budget-backups/db/"
else
    echo "[$(date)] ERROR: Backup failed!"
    exit 1
fi

# Clean old backups
find "$BACKUP_DIR" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete
echo "[$(date)] Cleaned backups older than $RETENTION_DAYS days"
```

### Point-in-Time Recovery

```ini
# my.cnf - Enable binary logging
[mysqld]
log_bin = /var/log/mysql/mysql-bin
binlog_format = ROW
expire_logs_days = 7
max_binlog_size = 100M
```

```bash
# Restore to specific point in time
# 1. Restore full backup
gunzip < backup.sql.gz | mysql -u root -p hr_budget

# 2. Apply binary logs up to specific time
mysqlbinlog \
    --stop-datetime="2025-01-15 14:30:00" \
    /var/log/mysql/mysql-bin.* | mysql -u root -p hr_budget
```

### PHP Backup Class

```php
class DatabaseBackup
{
    public function create(): string
    {
        $date = date('Ymd_His');
        $filename = "backup_{$date}.sql";
        $filepath = storage_path("backups/{$filename}");
        
        $host = env('DB_HOST');
        $name = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        
        $cmd = sprintf(
            'mysqldump --host=%s --user=%s --password=%s --single-transaction %s > %s',
            escapeshellarg($host),
            escapeshellarg($user),
            escapeshellarg($pass),
            escapeshellarg($name),
            escapeshellarg($filepath)
        );
        
        exec($cmd, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException('Backup failed');
        }
        
        // Compress
        $gzPath = "{$filepath}.gz";
        $fp = gzopen($gzPath, 'w9');
        gzwrite($fp, file_get_contents($filepath));
        gzclose($fp);
        unlink($filepath);
        
        return $gzPath;
    }
    
    public function restore(string $backupPath): void
    {
        if (!file_exists($backupPath)) {
            throw new \InvalidArgumentException('Backup file not found');
        }
        
        $host = env('DB_HOST');
        $name = env('DB_DATABASE');
        $user = env('DB_USERNAME');
        $pass = env('DB_PASSWORD');
        
        if (str_ends_with($backupPath, '.gz')) {
            $cmd = sprintf(
                'gunzip < %s | mysql --host=%s --user=%s --password=%s %s',
                escapeshellarg($backupPath),
                escapeshellarg($host),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($name)
            );
        } else {
            $cmd = sprintf(
                'mysql --host=%s --user=%s --password=%s %s < %s',
                escapeshellarg($host),
                escapeshellarg($user),
                escapeshellarg($pass),
                escapeshellarg($name),
                escapeshellarg($backupPath)
            );
        }
        
        exec($cmd, $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new \RuntimeException('Restore failed');
        }
    }
}
```

## 📁 File Backup

### Upload Files Backup

```bash
#!/bin/bash
# scripts/backup_files.sh

SOURCE_DIR="/var/www/hr_budget/storage/uploads"
BACKUP_DIR="/var/backups/hr_budget/files"
DATE=$(date +%Y%m%d)
ARCHIVE="$BACKUP_DIR/uploads_${DATE}.tar.gz"

mkdir -p "$BACKUP_DIR"

# Create compressed archive
tar -czf "$ARCHIVE" -C "$(dirname $SOURCE_DIR)" "$(basename $SOURCE_DIR)"

echo "[$(date)] Files backup created: $ARCHIVE"

# Sync to remote (optional)
# rsync -avz "$ARCHIVE" backup-server:/backups/hr_budget/
```

### Sync Pattern

```php
class FileBackup
{
    private string $sourceDir;
    private string $backupDir;
    
    public function __construct()
    {
        $this->sourceDir = storage_path('uploads');
        $this->backupDir = storage_path('backups/files');
    }
    
    public function sync(): array
    {
        $files = $this->getFilesToBackup();
        $synced = 0;
        
        foreach ($files as $file) {
            $relativePath = str_replace($this->sourceDir, '', $file);
            $backupPath = $this->backupDir . $relativePath;
            
            $backupDir = dirname($backupPath);
            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            if (!file_exists($backupPath) || 
                filemtime($file) > filemtime($backupPath)) {
                copy($file, $backupPath);
                $synced++;
            }
        }
        
        return [
            'total_files' => count($files),
            'synced' => $synced,
            'timestamp' => date('c')
        ];
    }
    
    private function getFilesToBackup(): array
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->sourceDir)
        );
        
        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
}
```

## 🔄 Recovery Procedures

### Recovery Checklist

```markdown
## Database Recovery Procedure

### 1. Assess the Situation
- [ ] Identify the cause of data loss
- [ ] Determine the recovery point (timestamp)
- [ ] Notify stakeholders

### 2. Prepare Environment
- [ ] Stop application (maintenance mode)
- [ ] Create backup of current (corrupted) state
- [ ] Verify backup file integrity

### 3. Execute Recovery
- [ ] Restore database from backup
- [ ] Apply binary logs if needed (PITR)
- [ ] Verify data integrity

### 4. Validate
- [ ] Check record counts
- [ ] Test critical queries
- [ ] Verify application functionality

### 5. Resume Operations
- [ ] Start application
- [ ] Monitor for issues
- [ ] Document incident
```

### Recovery Commands

```bash
# 1. Put app in maintenance mode
touch /var/www/hr_budget/storage/maintenance.flag

# 2. Backup current (corrupted) state
php scripts/backup.php --label=pre-recovery

# 3. Find the backup to restore
ls -la /var/backups/hr_budget/db/

# 4. Restore
gunzip < /var/backups/hr_budget/db/hr_budget_20250115_000000.sql.gz | mysql -u root -p hr_budget

# 5. Verify
mysql -u root -p hr_budget -e "SELECT COUNT(*) FROM users; SELECT COUNT(*) FROM budgets;"

# 6. Remove maintenance mode
rm /var/www/hr_budget/storage/maintenance.flag
```

## ✅ Testing & Validation

### Backup Verification Script

```php
class BackupVerifier
{
    public function verify(string $backupPath): array
    {
        $results = [
            'file_exists' => file_exists($backupPath),
            'file_size' => filesize($backupPath),
            'checksum' => md5_file($backupPath),
            'is_valid' => false,
            'tables' => []
        ];
        
        if (!$results['file_exists'] || $results['file_size'] < 1000) {
            return $results;
        }
        
        // Test restore to temporary database
        $tempDb = 'backup_verify_' . uniqid();
        
        try {
            Database::exec("CREATE DATABASE {$tempDb}");
            
            // Restore to temp database
            $cmd = sprintf(
                'gunzip < %s | mysql --host=%s --user=%s --password=%s %s',
                escapeshellarg($backupPath),
                escapeshellarg(env('DB_HOST')),
                escapeshellarg(env('DB_USERNAME')),
                escapeshellarg(env('DB_PASSWORD')),
                escapeshellarg($tempDb)
            );
            
            exec($cmd, $output, $returnCode);
            
            if ($returnCode === 0) {
                // Verify tables
                $tables = Database::query("SHOW TABLES FROM {$tempDb}")->fetchAll(\PDO::FETCH_COLUMN);
                $results['tables'] = $tables;
                $results['is_valid'] = count($tables) > 0;
            }
            
        } finally {
            // Clean up
            Database::exec("DROP DATABASE IF EXISTS {$tempDb}");
        }
        
        return $results;
    }
}
```

### Automated Backup Testing

```yaml
# .github/workflows/backup-test.yml
name: Backup Verification

on:
  schedule:
    - cron: '0 6 * * *'  # Daily at 6 AM

jobs:
  verify-backup:
    runs-on: ubuntu-latest
    steps:
      - name: Download latest backup
        run: |
          aws s3 cp s3://hr-budget-backups/db/latest.sql.gz ./backup.sql.gz
      
      - name: Verify backup
        run: |
          gunzip < backup.sql.gz | head -n 100
          gunzip -t backup.sql.gz
      
      - name: Test restore
        run: |
          mysql -e "CREATE DATABASE backup_test"
          gunzip < backup.sql.gz | mysql backup_test
          mysql backup_test -e "SHOW TABLES"
      
      - name: Notify on failure
        if: failure()
        run: |
          curl -X POST "${{ secrets.SLACK_WEBHOOK }}" \
            -d '{"text":"❌ Backup verification failed!"}'
```

### Recovery Drill Schedule

| Drill Type | Frequency | Description |
|:-----------|:----------|:------------|
| **Restore Test** | Monthly | Full database restore to test environment |
| **PITR Test** | Quarterly | Point-in-time recovery test |
| **Full DR Test** | Annually | Complete disaster recovery simulation |
