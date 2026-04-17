"""
Migration: Add organization_id to folders and files tables
Purpose: Enable organization-based file management
Author: Agent | Date: 2026-01-14
Safety: --confirm to execute. Default is dry-run.
"""
import mysql.connector
import sys
import argparse
from datetime import datetime

sys.path.append('.')
from db_config import get_db_config

LOG_FILE = "migration_add_org_to_files.log"

def log(msg, also_print=True):
    ts = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    with open(LOG_FILE, 'a', encoding='utf-8') as f:
        f.write(f"[{ts}] {msg}\n")
    if also_print:
        print(f"[{ts}] {msg}")

def get_db_connection():
    c = get_db_config()
    return mysql.connector.connect(
        host=c['host'], user=c['user'], password=c['password'],
        database=c['database'], charset='utf8mb4'
    )

def column_exists(cursor, table, column):
    cursor.execute(f"SHOW COLUMNS FROM {table} LIKE '{column}'")
    return cursor.fetchone() is not None

def index_exists(cursor, table, index_name):
    cursor.execute(f"SHOW INDEX FROM {table} WHERE Key_name = '{index_name}'")
    return cursor.fetchone() is not None

def constraint_exists(cursor, table, constraint_name):
    cursor.execute("""
        SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = %s 
        AND CONSTRAINT_NAME = %s
    """, (table, constraint_name))
    return cursor.fetchone()[0] > 0

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--confirm', action='store_true', help='Execute changes (default is dry-run)')
    args = parser.parse_args()
    is_dry_run = not args.confirm
    
    # Clear log
    with open(LOG_FILE, 'w', encoding='utf-8') as f:
        f.write('')
    
    log("=" * 60)
    log("Migration: Add organization_id to folders and files")
    log("=" * 60)
    log(f"Mode: {'[DRY-RUN] - No changes will be made' if is_dry_run else '[LIVE] - Changes will be applied'}")
    
    if is_dry_run:
        log(">> Add --confirm to execute changes")
    
    conn = get_db_connection()
    cursor = conn.cursor()
    
    try:
        changes = []
        
        # 1. Check/Add organization_id to folders
        log("\n--- Step 1: folders.organization_id ---")
        if column_exists(cursor, 'folders', 'organization_id'):
            log("[OK] Column folders.organization_id already exists (skipped)")
        else:
            log("[ADD] Will add: folders.organization_id INT NULL")
            changes.append(("folders", "ADD COLUMN organization_id INT NULL AFTER fiscal_year"))
        
        # 2. Check/Add FK constraint on folders
        if constraint_exists(cursor, 'folders', 'fk_folders_organization'):
            log("[OK] FK fk_folders_organization already exists (skipped)")
        else:
            log("[ADD] Will add: FK fk_folders_organization")
            changes.append(("folders", "ADD CONSTRAINT fk_folders_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL"))
        
        # 3. Check/Add organization_id to files
        log("\n--- Step 2: files.organization_id ---")
        if column_exists(cursor, 'files', 'organization_id'):
            log("[OK] Column files.organization_id already exists (skipped)")
        else:
            log("[ADD] Will add: files.organization_id INT NULL")
            changes.append(("files", "ADD COLUMN organization_id INT NULL AFTER folder_id"))
        
        # 4. Check/Add FK constraint on files
        if constraint_exists(cursor, 'files', 'fk_files_organization'):
            log("[OK] FK fk_files_organization already exists (skipped)")
        else:
            log("[ADD] Will add: FK fk_files_organization")
            changes.append(("files", "ADD CONSTRAINT fk_files_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL"))
        
        # 5. Check/Add indexes
        log("\n--- Step 3: Indexes ---")
        if index_exists(cursor, 'folders', 'idx_folders_org'):
            log("[OK] Index idx_folders_org already exists (skipped)")
        else:
            log("[ADD] Will add: INDEX idx_folders_org")
            changes.append(("folders", "ADD INDEX idx_folders_org (organization_id, fiscal_year)"))
        
        if index_exists(cursor, 'files', 'idx_files_org'):
            log("[OK] Index idx_files_org already exists (skipped)")
        else:
            log("[ADD] Will add: INDEX idx_files_org")
            changes.append(("files", "ADD INDEX idx_files_org (organization_id)"))
        
        # Execute changes
        log(f"\n--- Summary: {len(changes)} change(s) to apply ---")
        
        if len(changes) == 0:
            log("[OK] No changes needed. Database is already up to date.")
        elif is_dry_run:
            log("[DRY-RUN] Complete. No changes made.")
            log("Run with --confirm to apply changes.")
        else:
            for table, alter_clause in changes:
                sql = f"ALTER TABLE {table} {alter_clause}"
                log(f"Executing: {sql}")
                cursor.execute(sql)
            
            conn.commit()
            log("[SUCCESS] All changes committed!")
        
    except Exception as e:
        conn.rollback()
        log(f"[ERROR] {e}")
        import traceback
        log(traceback.format_exc())
        sys.exit(1)
    finally:
        cursor.close()
        conn.close()
        log(f"\n[LOG] Saved to: {LOG_FILE}")

if __name__ == "__main__":
    main()
