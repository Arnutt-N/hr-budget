# Migration: Add organization_id to folders and files tables
# Date: 2026-01-14
# Purpose: Enable organization-based file management

## Instructions
Run this SQL in phpMyAdmin or MySQL CLI:

```sql
-- 1. Add organization_id to folders table
ALTER TABLE folders 
ADD COLUMN organization_id INT NULL AFTER fiscal_year,
ADD CONSTRAINT fk_folders_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE SET NULL;

-- 2. Add organization_id to files table
ALTER TABLE files 
ADD COLUMN organization_id INT NULL AFTER folder_id,
ADD CONSTRAINT fk_files_organization 
    FOREIGN KEY (organization_id) REFERENCES organizations(id) 
    ON DELETE SET NULL;

-- 3. Create index for faster queries
CREATE INDEX idx_folders_org ON folders(organization_id, fiscal_year);
CREATE INDEX idx_files_org ON files(organization_id);
```

## Rollback (if needed)
```sql
ALTER TABLE folders DROP FOREIGN KEY fk_folders_organization;
ALTER TABLE folders DROP COLUMN organization_id;
ALTER TABLE files DROP FOREIGN KEY fk_files_organization;
ALTER TABLE files DROP COLUMN organization_id;
```
