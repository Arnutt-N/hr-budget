---
description: ขั้นตอนการใช้ Python จัดการข้อมูลในฐานข้อมูล (Python Data Management) อย่างรัดกุมและปลอดภัย
---

# Robust Python Data Management Workflow

## Version / Dependency Check

```bash
python --version
pip freeze | grep -E 'mysql-connector-python|pandas|matplotlib|seaborn|plotly|openpyxl|jupyter'
pip show mysql-connector-python
```

## กฎเหล็กความปลอดภัย (Golden Rules) 🛡️

1.  **Backup Before Write/Delete**: ต้องสำรองฐานข้อมูลก่อนรัน script ที่มีการแก้ไขข้อมูลเสมอ
2.  **Dry-Run First**: ต้องมี mode `--dry-run` เพื่อทดสอบผลลัพธ์ก่อนเสมอ
3.  **Explicit Commit**: ห้ามใช้ `autocommit` ต้องสั่ง `conn.commit()` เองเมื่อมั่นใจเท่านั้น
4.  **Logging**: ต้องบันทึก Log ทุกการเปลี่ยนแปลง (Old Value -> New Value)
5.  **Sanity Check**: ต้องมี limit ป้องกันการแก้ไขข้อมูลจำนวนมากเกินผิดปกติ (Mass Edit Prevention)

## โครงสร้างไฟล์แนะนำ

```
python/
├── db_config.py          # Database configuration
├── venv/                 # Virtual environment
├── your_script.py        # Main script
├── your_script.log       # Log file
└── backups/              # Directory for pre-run sql dumps (optional)
```

## ขั้นตอนการสร้าง Script (Robust Template)

### 1. Robust Script Template

ใช้ template นี้เพื่อความปลอดภัยสูงสุด:

```python
"""
[Script Purpose Description]

Author: [Your Name]
Date: [YYYY-MM-DD]
Safety: Requires --confirm to execute changes. Default is dry-run.
"""

import mysql.connector
import sys
import argparse
import time
from datetime import datetime
sys.path.append('.')
from db_config import get_db_config

LOG_FILE = "script_output.log"
SAFETY_LIMIT = 1000  # Max rows allowed to be changed

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
# NOTE: Explicit transaction handling is performed in `main()`.
# `conn.start_transaction()` is called before any modifications.
# On error, `conn.rollback()` will revert changes.

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
        time.sleep(1) # Intentional delay to read warning

    conn = get_db_connection()
    cursor = conn.cursor() # Use dictionary=True if needed
    
    try:
        conn.start_transaction()
        
        # ---------------------------------------------------------
        # 1. Pre-flight Checks (ตรวจสอบความพร้อมก่อนเริ่ม)
        # ---------------------------------------------------------
        # Example: Check table exists or check specific condition
        # check_sanity(cursor)
        
        # ---------------------------------------------------------
        # 2. Main Logic
        # ---------------------------------------------------------
        changes_count = 0
        
        # Example Loop
        # items = fetch_items(cursor)
        # for item in items:
        #     if should_update(item):
        #         if not is_dry_run:
        #             update_item(cursor, item)
        #         log(f"Processing ID {item[0]}: Will update...")
        #         changes_count += 1
        
        # ---------------------------------------------------------
        # 3. Safety Check (Sanity Check)
        # ---------------------------------------------------------
        if changes_count > SAFETY_LIMIT:
            raise Exception(f"❌ Safety Limit Exceeded! Attempting to change {changes_count} rows (Limit: {SAFETY_LIMIT})")
        
        # ---------------------------------------------------------
        # 4. Final Commit / Rollback
        # ---------------------------------------------------------
        if is_dry_run:
            conn.rollback()
            log("\n🔍 Dry-run complete. No changes made.")
        else:
            conn.commit()
            log("\n✅ LIVE EXECUTION COMPLETE. Changes committed.")
            
    except Exception as e:
        conn.rollback()
        log(f"\n❌ Error: {e}")
        import traceback
        log(traceback.format_exc())
    finally:
        cursor.close()
        conn.close()
        log(f"📄 Log saved to: {LOG_FILE}")

if __name__ == "__main__":
    main()
```

## Checklist ความปลอดภัยก่อนรัน (Pre-Flight Checklist)

ก่อนรันคำสั่งที่มีผลเปลี่ยนแปลงข้อมูล (`--confirm`) ต้องทำตามนี้:

- [ ] **Backup**: ได้สำรองฐานข้อมูลล่าสุดแล้วหรือยัง? (ใช้ `/backup-procedure`)
- [ ] **Dry-Run**: รันแบบปกติ (Dry-run) แล้วตรวจสอบ Log ว่าสิ่งที่เปลี่ยนถูกต้องไหม?
- [ ] **Code Review**: ตรวจสอบ logic ใน script ว่า `WHERE` clause ครอบคลุมถูกต้อง ไม่ update ทั้งตาราง
- [ ] **Production Check**: แน่ใจนะว่าเชื่อมต่อกับ Database ถูกตัว (Dev vs Update)?

## Best Practices เพิ่มเติม

### 1. Mass Update Protection
หากต้องการ update ข้อมูลจำนวนมาก (> 10%) ควรมี confirmation prompt:

```python
if changes_count > 100 and not is_dry_run:
    user_input = input(f"⚠️ Warning: You are about to update {changes_count} rows. Type 'yes' to proceed: ")
    if user_input.lower() != 'yes':
        raise Exception("Aborted by user.")
```

### 2. Log Old vs New Values
สำคัญมากสำหรับการ Audit ให้บันทึกค่าเก่าและใหม่เสมอ:

```python
# Fetch Current first
cursor.execute("SELECT val FROM table WHERE id = %s", (id,))
old_val = cursor.fetchone()[0]

# Update
cursor.execute("UPDATE ...")

log(f"[ID: {id}] Changed '{old_val}' -> '{new_val}'")
```

### 3. Idempotency (รันซ้ำได้)
เขียน script ให้รันซ้ำได้โดยไม่พัง (Idempotent):

```python
# ดี: ตรวจก่อน insert
cursor.execute("INSERT INTO ... SELECT ... WHERE NOT EXISTS ...")

# ดี: Update เฉพาะที่ค่าเปลี่ยนจริง
cursor.execute("UPDATE ... SET val = %s WHERE id = %s AND val != %s", (new, id, new))
```

## Troubleshooting

### Connection Failed
```python
try:
    conn = get_db_connection()
except mysql.connector.Error as err:
    log(f"❌ Connection error: {err}")
    sys.exit(1)
```

### Encoding Error
When reading/writing files ensure UTF‑8 encoding:
```python
df.to_csv('reports/data.csv', encoding='utf-8-sig')
```

### Transaction Deadlock
Handle deadlocks with retry logic:
```python
import time

MAX_RETRIES = 3
for attempt in range(MAX_RETRIES):
    try:
        conn.start_transaction()
        # ... your operations ...
        conn.commit()
        break
    except mysql.connector.Error as err:
        if err.errno == 1213:  # Deadlock
            conn.rollback()
            if attempt < MAX_RETRIES - 1:
                log(f"⚠️ Deadlock detected, retrying ({attempt+1}/{MAX_RETRIES})...")
                time.sleep(0.1 * (attempt + 1))  # Exponential backoff
            else:
                log("❌ Max retries reached")
                raise
        else:
            conn.rollback()
            raise
```

## Advanced Patterns

### Bulk INSERT Pattern
```python
# Efficient batch insert
data = [(1, 'name1'), (2, 'name2'), (3, 'name3')]  # Your data
BATCH_SIZE = 500

for i in range(0, len(data), BATCH_SIZE):
    batch = data[i:i+BATCH_SIZE]
    if not is_dry_run:
        cursor.executemany(
            "INSERT INTO items (id, name) VALUES (%s, %s)",
            batch
        )
    log(f"Inserted batch {i//BATCH_SIZE + 1}: {len(batch)} rows")
```

## Performance Tips

| เทคนิค | คำอธิบาย |
|--------|----------|
| **Batch Operations** | ใช้ `executemany()` แทน loop ของ `execute()` |
| **Connection Pooling** | ใช้ `mysql.connector.pooling.MySQLConnectionPool` สำหรับ script ที่รันบ่อย |
| **Index Awareness** | ตรวจสอบว่า WHERE clause ใช้ indexed columns |
| **LIMIT Queries** | ใส่ LIMIT เมื่อทดสอบ query ใหม่ |
| **Avoid SELECT \*** | เลือกเฉพาะ columns ที่ต้องการ |

## DELETE Pattern Example
```python
# Safe DELETE with existence check
cursor.execute("SELECT COUNT(*) FROM budget_category_items WHERE id = %s", (item_id,))
if cursor.fetchone()[0] > 0:
    if not is_dry_run:
        cursor.execute("DELETE FROM budget_category_items WHERE id = %s", (item_id,))
        # If an error occurs, the outer transaction will be rolled back
    log(f"✅ Deleted item ID {item_id}")
else:
    log(f"⚠️ Item ID {item_id} does not exist")
```

## Reference Links
- 📄 `add_special_profession_subitems.py` – example script that adds sub‑items with idempotent checks and logging. Path: `python/add_special_profession_subitems.py`
- 📚 MySQL Connector/Python docs: https://dev.mysql.com/doc/connector-python/en/

## สรุปคำสั่ง

```bash
# 1. ทดสอบก่อน (Safe)
python my_script.py

# 2. รันจริง (Dangerous)
python my_script.py --confirm
```
