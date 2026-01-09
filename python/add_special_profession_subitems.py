"""
Add Budget Sub-items for Special Professions

This script adds two new sub-items under the category:
"ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)"

New items:
1. ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์
2. ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก
"""

import mysql.connector
import sys
from datetime import datetime
sys.path.append('.')
from db_config import get_db_config

LOG_FILE = "add_subitems_result.log"

def log(message, also_print=True):
    """Log message to file and optionally print to console."""
    with open(LOG_FILE, 'a', encoding='utf-8') as f:
        f.write(message + '\n')
    if also_print:
        print(message)

def get_db_connection():
    config = get_db_config()
    return mysql.connector.connect(
        host=config['host'],
        user=config['user'],
        password=config['password'],
        database=config['database'],
        charset='utf8mb4'
    )

def find_parent_category(cursor):
    """Find the parent category for special professions."""
    search_pattern = '%ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ (วช) /เชี่ยวชาญเฉพาะ (ชช.)%'
    
    cursor.execute("""
        SELECT id, name, level
        FROM budget_category_items
        WHERE name LIKE %s
    """, (search_pattern,))
    
    result = cursor.fetchone()
    if result:
        return {
            'id': result[0],
            'name': result[1],
            'level': result[2]
        }
    return None

def check_if_exists(cursor, name, parent_id):
    """Check if a sub-item already exists."""
    cursor.execute("""
        SELECT COUNT(*) 
        FROM budget_category_items 
        WHERE name = %s AND parent_id = %s
    """, (name, parent_id))
    
    count = cursor.fetchone()[0]
    return count > 0

def insert_sub_item(cursor, name, parent_id, parent_level, sort_order):
    """Insert a new sub-item."""
    now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    
    cursor.execute("""
        INSERT INTO budget_category_items 
        (name, parent_id, level, is_active, sort_order, created_at, updated_at)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
    """, (name, parent_id, parent_level + 1, 1, sort_order, now, now))
    
    return cursor.lastrowid

def main():
    # Clear log file
    with open(LOG_FILE, 'w', encoding='utf-8') as f:
        f.write('')
    
    log("=" * 60)
    log("Adding Special Profession Sub-items")
    log("=" * 60)
    
    conn = get_db_connection()
    cursor = conn.cursor()
    
    try:
        # Step 1: Find parent category
        log("\n[Step 1] Finding parent category...")
        parent = find_parent_category(cursor)
        
        if not parent:
            log("❌ Error: Parent category not found!")
            return
        
        log(f"✅ Found parent:")
        log(f"   ID: {parent['id']}")
        log(f"   Name: {parent['name']}")
        log(f"   Level: {parent['level']}")
        
        # Step 2: Define new sub-items
        new_items = [
            {
                'name': 'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งนักวิชาการคอมพิวเตอร์',
                'sort_order': 1
            },
            {
                'name': 'ค่าตอบแทนรายเดือนเท่ากับเงินประจำตำแหน่งประเภทวิชาชีพเฉพาะ ตำแหน่งวิศวกร/สถาปนิก',
                'sort_order': 2
            }
        ]
        
        # Step 3: Insert each item
        log(f"\n[Step 2] Inserting {len(new_items)} sub-items...")
        inserted_count = 0
        skipped_count = 0
        
        for item in new_items:
            log(f"\n   Processing: {item['name'][:50]}...")
            
            if check_if_exists(cursor, item['name'], parent['id']):
                log(f"   ⚠️  Already exists, skipping.")
                skipped_count += 1
                continue
            
            new_id = insert_sub_item(
                cursor, 
                item['name'], 
                parent['id'], 
                parent['level'], 
                item['sort_order']
            )
            log(f"   ✅ Inserted with ID: {new_id}")
            inserted_count += 1
        
        # Commit the transaction
        conn.commit()
        
        # Step 4: Verification
        log("\n" + "=" * 60)
        log("Verification")
        log("=" * 60)
        
        cursor.execute("""
            SELECT id, name, level, sort_order
            FROM budget_category_items
            WHERE parent_id = %s
            ORDER BY sort_order, id
        """, (parent['id'],))
        
        children = cursor.fetchall()
        log(f"\nParent: {parent['name']}")
        log(f"Children ({len(children)}):")
        for child in children:
            log(f"  - [{child[0]}] {child[1]}")
            log(f"    Level: {child[2]}, Sort: {child[3]}")
        
        log("\n" + "=" * 60)
        log("Summary")
        log("=" * 60)
        log(f"✅ Inserted: {inserted_count}")
        log(f"⚠️  Skipped: {skipped_count}")
        log(f"✅ Total children: {len(children)}")
        log(f"\n📄 Log saved to: {LOG_FILE}")
        
    except Exception as e:
        conn.rollback()
        log(f"\n❌ Error occurred: {e}")
        import traceback
        log(traceback.format_exc())
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()
