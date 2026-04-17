---
name: database_assistant
description: Guide for database management, migration workflow, and schema design in HR Budget project.
---

# Database Assistant

Guide for managing the MySQL database, handling migrations, and ensuring data integrity.

## 📑 Table of Contents
- [Overview](#-overview)
- [Migration Workflow](#-migration-workflow)
- [Schema Standards](#-schema-standards)
- [Backup & Restore](#-backup--restore)
- [Key Tables](#-key-tables)
- [Troubleshooting](#-troubleshooting)

## 🗄️ Overview

| Component | Detail |
|:----------|:-------|
| **Engine** | MySQL 8.0+ |
| **Migrations** | Raw SQL files in `database/migrations/` |
| **Runner** | Custom PHP/Shell scripts |
| **Hierarchy** | 3-Tier (Plans > Projects > Activities) |

## 🔄 Migration Workflow

### 1. Create Migration File
Create a new file in `database/migrations/` using prefix `XXX_description.sql`.

**Naming Convention:**
- Format: `XXX_snake_case_description.sql`
- Example: `055_add_status_column_to_projects.sql`

**Template:**
```sql
-- Up
ALTER TABLE projects ADD COLUMN status ENUM('draft', 'active', 'archived') DEFAULT 'draft';

-- Down (Commented out usually, but good to have)
-- ALTER TABLE projects DROP COLUMN status;
```

### 2. Run Migrations
Use the provided batch script.

```cmd
cd c:\laragon\www\hr_budget\database
run_migrations.bat
```

> **Note:** The runner keeps track of executed files. It won't re-run old migrations.

### 3. Verify Changes
Check the database schema directly or use `check_schema.php`.

```cmd
cd c:\laragon\www\hr_budget\public
php check_schema.php
```

## 🏗️ Schema Standards

### Naming Conventions
- **Tables**: Plural, snake_case (e.g., `budget_requests`, `users`)
- **Primary Key**: `id` INT AUTO_INCREMENT
- **Foreign Keys**: `singular_table_id` (e.g., `user_id`, `project_id`)
- **Timestamps**: `created_at` (TIMESTAMP DEFAULT NOW), `updated_at`

### Best Practices

1. **CharSets**: Always use `utf8mb4` and `utf8mb4_unicode_ci` for Thai support.
   ```sql
   CREATE TABLE items (
       id INT AUTO_INCREMENT PRIMARY KEY,
       name VARCHAR(255) NOT NULL
   ) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Foreign Keys**: Always define explicit constraints.
   ```sql
   CONSTRAINT fk_project_user 
   FOREIGN KEY (user_id) REFERENCES users(id) 
   ON DELETE CASCADE
   ```

3. **Soft Deletes**: Use `deleted_at` (TIMESTAMP NULL) for critical data.
   - Do NOT physically delete rows unless necessary.

4. **Indexes**: Add indexes for frequently searched columns (`is_active`, `fiscal_year`).

## 💾 Backup & Restore

> See full workflow: [/backup-procedure]

### Quick Dump (Manual)
```cmd
mysqldump -u root -p hr_budget > backup.sql
```

### Quick Restore (Manual)
```cmd
mysql -u root -p hr_budget < backup.sql
```

## 🔑 Key Tables

### Hierarchy
- `plans` (Strategic)
- `projects` (Outputs/Projects) - *Recursive*
- `activities` (Operational) - *Recursive*

### Budget
- `budget_requests`
- `budget_allocations`
- `budget_executions`

### Configuration
- `fiscal_years`
- `fund_sources`
- `users` (Roles: admin, approver, user, viewer)

## 🚨 Troubleshooting

### Common Errors

| Error | Solution |
|:------|:---------|
| **Foreign key constraint fails** | Check if referenced ID exists. Ensure table types match (INT vs BIGINT). |
| **Duplicate entry** | Check UNIQUE indexes. |
| **Data too long** | Increase VARCHAR size or use TEXT. |
| **Incorrect string value** | Check encoding. Must be `utf8mb4_unicode_ci`. |

### Scripts Reference
- `python/db_config.py` - Database connection config
- `python/analyze_db_schema.py` - Generate schema report
- `python/migrate.php` - Custom migration logic
