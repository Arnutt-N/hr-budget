"""
Add org_id column to budget_requests table

Author: Antigravity AI
Date: 2026-01-10
Safety: Requires --confirm to execute changes. Default is dry-run.
"""

import mysql.connector
import sys
import argparse
import time
from datetime import datetime
sys.path.append('.')
from db_config import get_db_config

LOG_FILE = "add_org_id_column.log"

def log(message, also_print=True):
    """Log to file and optionally print."""
    timestamp = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    with open(LOG_FILE, 'a', encoding='utf-8') as f:
        f.write(f"[{timestamp}] {message}\n")
    if also_print:
        print(f"[{timestamp}] {message}")

def get_db_connection():
    config = get_db_config()
    return mysql.connector.connect(
        host=config['host'], user=config['user'],
        password=config['password'], database=config['database'],
        charset='utf8mb4'
    )

def check_column_exists(cursor):
    """Check if org_id column already exists"""
    cursor.execute("SHOW COLUMNS FROM budget_requests LIKE 'org_id'")
    return cursor.fetchone() is not None

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--confirm', action='store_true', help='Execute changes (Commit)')
    args = parser.parse_args()
    
    is_dry_run = not args.confirm
    
    # Clear log for new run
    with open(LOG_FILE, 'w', encoding='utf-8') as f:
        f.write('')

    log("=" * 60)
    log(f"Starting Script (Mode: {'DRY-RUN' if is_dry_run else 'LIVE EXECUTION'})")
    log("=" * 60)
    
    if is_dry_run:
        log("⚠️  To execute changes, add flag: --confirm")
        time.sleep(1)

    conn = get_db_connection()
    cursor = conn.cursor()
    
    try:
        # Check if column already exists
        if check_column_exists(cursor):
            log("✓ Column org_id already exists. Nothing to do.")
            return
        
        log("⚙️  Column org_id does not exist. Preparing to add...")
        
        if not is_dry_run:
            # MySQL Connector already in transaction mode, no need to start_transaction()
            
            # Add column
            log("📝 Adding org_id column...")
            cursor.execute("""
                ALTER TABLE budget_requests 
                ADD COLUMN org_id INT NULL AFTER created_by
            """)
            log("✅ Column added successfully")
            
            # Add foreign key constraint
            log("📝 Adding foreign key constraint...")
            cursor.execute("""
                ALTER TABLE budget_requests
                ADD CONSTRAINT fk_budget_requests_org_id 
                FOREIGN KEY (org_id) REFERENCES organizations(id) ON DELETE SET NULL
            """)
            log("✅ Foreign key constraint added successfully")
            
            conn.commit()
            log("\n✅ LIVE EXECUTION COMPLETE. Changes committed.")
        else:
            log("\n🔍 Dry-run complete. Would execute:")
            log("   1. ALTER TABLE budget_requests ADD COLUMN org_id INT NULL")
            log("   2. ALTER TABLE budget_requests ADD CONSTRAINT fk_budget_requests_org_id")
            log("\n   Run with --confirm to apply changes")
            
    except Exception as e:
        if not is_dry_run:
            conn.rollback()
        log(f"\n❌ Error: {e}")
        import traceback
        log(traceback.format_exc())
        sys.exit(1)
    finally:
        cursor.close()
        conn.close()
        log(f"📄 Log saved to: {LOG_FILE}")

if __name__ == "__main__":
    main()
