---
description: ขั้นตอนการใช้ Python จัดการข้อมูลในฐานข้อมูล (Python Data Management) อย่างรัดกุมและปลอดภัย
---

# Robust Python Data Management Workflow

## กฎเหล็กความปลอดภัย (Golden Rules) 🛡️

1. **Backup Before Write/Delete**: สำรอง DB ก่อนรัน script แก้ไขข้อมูลเสมอ
2. **Dry-Run First**: ต้องมี `--confirm` flag, default = dry-run
3. **Explicit Commit**: ห้าม autocommit, สั่ง `conn.commit()` เองเท่านั้น
4. **Logging**: บันทึก Log ทุกการเปลี่ยนแปลง (Old → New)
5. **Sanity Check**: มี limit ป้องกัน mass edit

## 🤖 AI Agent Fallback Protocol

> [!IMPORTANT]
> **หาก Python script อัตโนมัติล้มเหลว/Hang** → ห้าม Retry ซ้ำ → แจ้ง User ทันทีพร้อมคำสั่ง Manual

### Manual Execution (Windows venv)
```bash
cd C:\laragon\www\hr_budget\python
venv\Scripts\activate
python your_script.py           # Dry-run
python your_script.py --confirm # Execute
type your_script.log            # Check log
```

### Common Issues

| ปัญหา | วิธีแก้ |
|-------|--------|
| Script Hang | Terminate & retry manually |
| `ModuleNotFoundError` | รัน `venv\Scripts\activate` ก่อน |
| `Transaction already in progress` | ลบ `conn.start_transaction()` |
| Permission Denied | ปิด phpMyAdmin/app อื่นที่ใช้ DB |

## โครงสร้างไฟล์

```
python/
├── db_config.py    # Database config
├── venv/           # Virtual environment
├── your_script.py  # Main script
└── your_script.log # Log file
```

## Script Template

```python
"""
[Purpose] | Author: [Name] | Date: [YYYY-MM-DD]
Safety: --confirm to execute. Default is dry-run.
"""
import mysql.connector, sys, argparse, time
from datetime import datetime
sys.path.append('.')
from db_config import get_db_config

LOG_FILE = "script_output.log"
SAFETY_LIMIT = 1000

def log(msg, also_print=True):
    ts = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    with open(LOG_FILE, 'a', encoding='utf-8') as f:
        f.write(f"[{ts}] {msg}\n")
    if also_print: print(f"[{ts}] {msg}")

def get_db_connection():
    c = get_db_config()
    return mysql.connector.connect(
        host=c['host'], user=c['user'], password=c['password'],
        database=c['database'], charset='utf8mb4'
    )

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--confirm', action='store_true')
    args = parser.parse_args()
    is_dry_run = not args.confirm
    
    with open(LOG_FILE, 'w') as f: f.write('')
    log("=" * 50)
    log(f"Mode: {'DRY-RUN' if is_dry_run else 'LIVE'}")
    if is_dry_run: log("⚠️ Add --confirm to execute"); time.sleep(1)
    
    conn = get_db_connection()
    cursor = conn.cursor()
    
    try:
        changes_count = 0
        # === YOUR LOGIC HERE ===
        # if not is_dry_run: cursor.execute(...)
        # changes_count += 1
        
        if changes_count > SAFETY_LIMIT:
            raise Exception(f"❌ Safety limit exceeded: {changes_count}")
        
        if is_dry_run:
            conn.rollback()
            log("🔍 Dry-run complete. No changes.")
        else:
            conn.commit()
            log("✅ Changes committed.")
    except Exception as e:
        conn.rollback()
        log(f"❌ Error: {e}")
        import traceback; log(traceback.format_exc())
    finally:
        cursor.close(); conn.close()
        log(f"📄 Log: {LOG_FILE}")

if __name__ == "__main__": main()
```

## Pre-Flight Checklist

- [ ] **Backup**: สำรอง DB แล้ว? (`/backup-procedure`)
- [ ] **Dry-Run**: รันปกติแล้วตรวจ Log?
- [ ] **WHERE clause**: ถูกต้อง ไม่ update ทั้งตาราง?

## Best Practices

```python
# Mass update protection
if changes_count > 100 and not is_dry_run:
    if input("Update {changes_count} rows? (yes): ").lower() != 'yes':
        raise Exception("Aborted")

# Log old vs new
old = cursor.execute("SELECT val FROM t WHERE id=%s", (id,)).fetchone()[0]
log(f"[{id}] '{old}' → '{new}'")

# Idempotent insert
cursor.execute("INSERT INTO ... SELECT ... WHERE NOT EXISTS ...")
```

## Troubleshooting

```python
# Connection error
try: conn = get_db_connection()
except mysql.connector.Error as e: log(f"❌ {e}"); sys.exit(1)

# UTF-8 encoding
df.to_csv('data.csv', encoding='utf-8-sig')
```

## สรุปคำสั่ง

```bash
python my_script.py           # Dry-run (Safe)
python my_script.py --confirm # Execute (Dangerous)
```
