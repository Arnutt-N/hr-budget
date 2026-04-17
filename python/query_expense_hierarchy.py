"""
Query Expense Types Hierarchy | Author: AI Agent | Date: 2026-01-11
Purpose: Fetch expense types, groups, and items structure for summary cards planning.
Mode: READ-ONLY (no --confirm needed)
"""
import mysql.connector
import sys
from datetime import datetime

sys.path.append('.')
from db_config import get_db_config

LOG_FILE = "expense_types_query.log"

def log(msg, also_print=True):
    ts = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    with open(LOG_FILE, 'a', encoding='utf-8') as f:
        f.write(f"[{ts}] {msg}\n")
    if also_print:
        print(msg)

def get_db_connection():
    c = get_db_config()
    return mysql.connector.connect(
        host=c['host'], user=c['user'], password=c['password'],
        database=c['database'], charset='utf8mb4'
    )

def main():
    # Clear log
    with open(LOG_FILE, 'w', encoding='utf-8') as f:
        f.write('')
    
    log("=" * 60)
    log("EXPENSE TYPES HIERARCHY ANALYSIS")
    log("=" * 60 + "\n")
    
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    try:
        # Get all expense types
        cursor.execute("""
            SELECT id, name_th 
            FROM expense_types 
            WHERE is_active=1 
            ORDER BY sort_order
        """)
        types = cursor.fetchall()
        
        for t in types:
            log(f"\n📁 TYPE {t['id']}: {t['name_th']}")
            log("-" * 50)
            
            # Get groups for this type
            cursor.execute("""
                SELECT id, name_th 
                FROM expense_groups 
                WHERE expense_type_id=%s AND is_active=1 
                ORDER BY sort_order
            """, (t['id'],))
            groups = cursor.fetchall()
            
            if not groups:
                log("   (No groups)")
                continue
            
            for g in groups:
                log(f"\n   📂 [Group] {g['name_th']}")
                
                # Get TOP-LEVEL items (parent_id IS NULL)
                cursor.execute("""
                    SELECT id, name_th 
                    FROM expense_items 
                    WHERE expense_group_id=%s 
                      AND deleted_at IS NULL 
                      AND parent_id IS NULL 
                    ORDER BY sort_order
                """, (g['id'],))
                items = cursor.fetchall()
                
                if not items:
                    log("      (No items)")
                    continue
                
                for i in items:
                    log(f"      🔹 {i['name_th']}")
                    
                    # Get children of this item
                    cursor.execute("""
                        SELECT id, name_th 
                        FROM expense_items 
                        WHERE parent_id=%s 
                          AND deleted_at IS NULL 
                        ORDER BY sort_order 
                        LIMIT 5
                    """, (i['id'],))
                    children = cursor.fetchall()
                    
                    for c in children:
                        log(f"         └─ {c['name_th']}")
        
        log("\n" + "=" * 60)
        log("ANALYSIS COMPLETE")
        log(f"📄 Log saved to: {LOG_FILE}")
        
    except Exception as e:
        log(f"❌ Error: {e}")
        import traceback
        log(traceback.format_exc())
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()
