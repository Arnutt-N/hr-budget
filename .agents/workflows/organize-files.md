---
description: ขั้นตอนการจัดระเบียบไฟล์และโฟลเดอร์ (Strict File Organization)
---

# 📂 ขั้นตอนการจัดระเบียบไฟล์ (Strict File Organization)

Workflow นี้ใช้สำหรับจัดระเบียบไฟล์ให้ตรงตามมาตรฐาน `folder-structure.md` อย่างเคร่งครัด

> [!IMPORTANT]
> ห้ามลบไฟล์ทิ้งหากไม่มั่นใจ ให้ย้ายไปที่ `archives/unused/` แทนเสมอ

## 0. 🔧 Pre‑Check (เตรียมสภาพแวดล้อม)

1. ตรวจสอบว่าอยู่ในโฟลเดอร์โปรเจคหลัก  
   ```cmd
   cd "c:\laragon\www\hr_budget"
   ```  
2. ตรวจสอบไม่มีไฟล์เปิดอยู่ใน IDE หรือโปรเซสอื่น  
   ```cmd
   tasklist | findstr /i "phpstorm\|vscode"
   ```  
3. สร้างไฟล์ Log เริ่มต้น  
   ```cmd
   echo "=== เริ่มการจัดระเบียบไฟล์: %date% %time% ===" >> project-log-md\organize-log.md
   ```

## 1. 🔍 วิเคราะห์ประเภทไฟล์ (Analyze)
ตรวจสอบเนื้อหาและวัตถุประสงค์ของไฟล์ เพื่อเลือกปลายทางที่ถูกต้อง:

| ประเภทไฟล์ | โฟลเดอร์ปลายทาง | ตัวอย่าง |
|------------|-----------------|----------|
| เอกสารวิจัย, Analysis, Schema Doc | `research/` (local-only, git-ignored) | `schema_analysis.md`, `requirements.md` |
| แผนงาน, Proposal, Implementation Plan | `PRPs/` | `phase_*.md`, `status_*.md` |
| ตัวอย่าง code, Reference, UI Mockup | `examples/` | `wireframe_*.html`, `sample_*.php` |
| Logs, Task lists, Walkthroughs | `project-log-md/` | `2026-01-*_session_*.md`, `backup-log.md` |
| Python scripts/logs/venvs | `python/` | `*.py`, `venv/`, `*.ipynb` |
| Utility scripts (.bat, .sh, migration) | `scripts/` | `run_*.bat`, `migrate_*.php`, `*.sh` |
| Spec, Requirement, Manual, API Doc | `docs/` | `README.md`, `API_GUIDE.md` |
| **ไฟล์ที่ไม่ใช้แล้ว / Code เก่า** | `archives/unused/` | `old_*.php`, deprecated files |
| **ไฟล์ Test ชั่วคราว** | `archives/test/` | `test_*.php`, `check_*.php`, `verify_*.php` |
| **ไฟล์ Backup** | `archives/backup/` | Backup folders only |

> [!WARNING]
> **ไฟล์ Root ที่ห้ามย้าย**: `index.php`, `composer.json`, `.env`, `.gitignore`, `package.json`

## 2. 📋 สร้างรายการไฟล์ที่จะย้าย (List Files)
ก่อนย้าย ให้ทำรายการเพื่อตรวจสอบและ Confirm กับ User:

```markdown
### ไฟล์ที่จะย้าย:
- [ ] `check_cols.php` → `archives/test/`
- [ ] `verify_fix.php` → `archives/test/`
- [ ] `backup_script.php` → `scripts/` (ถ้ามีประโยชน์) หรือ `archives/unused/` (ถ้าไม่ใช้)
```

## 3. 🚚 ดำเนินการย้าย (Move)

### 3.1 คำสั่งย้ายไฟล์ (Windows)
```cmd
move "source_file.php" "destination_folder\"
```

### 3.2 คำสั่งย้ายหลายไฟล์พร้อมกัน
```cmd
move check_*.php archives\test\
move verify_*.php archives\test\
```

### 3.3 กรณีไฟล์สำคัญ - ใช้ Copy ก่อน
```cmd
copy "important_file.php" "destination_folder\"
REM ตรวจสอบแล้วค่อยลบต้นฉบับ
del "important_file.php"
```

> [!CAUTION]
> **ห้ามใช้ `/Y` flag** (force overwrite) เพื่อป้องกันการเขียนทับไฟล์โดยไม่ตั้งใจ

## 4. 🕵️‍♂️ ตรวจสอบ (Verification) **[MANDATORY]**
**ห้ามข้ามขั้นตอนนี้เด็ดขาด - ต้องทำทุกครั้ง**

### 4.1 ตรวจสอบด้วย list_dir
```
list_dir("c:\laragon\www\hr_budget\archives\test")
```
- ✅ **ผ่าน**: เห็นไฟล์ที่ย้ายมา
- ❌ **ไม่ผ่าน**: ไม่เห็นไฟล์ = **FAILED** ต้องหาสาเหตุ

### 4.2 ตรวจสอบเนื้อหาไฟล์
```
view_file("c:\laragon\www\hr_budget\archives\test\check_cols.php")
```
- อ่านได้ปกติ
- เนื้อหาครบถ้วน

### 4.3 ตรวจสอบต้นทาง (ถ้าเป็นการ Move)
```
list_dir("c:\laragon\www\hr_budget")
```
- ✅ **ผ่าน**: ไม่เห็นไฟล์ที่ต้นทางแล้ว
- ❌ **ไม่ผ่าน**: ยังเห็นไฟล์อยู่ = ย้ายไม่สำเร็จ

## 5. 🧹 เก็บกวาด (Cleanup)
- ลบโฟลเดอร์ว่างเปล่า (ถ้ามี)
- **ห้ามลบโฟลเดอร์หลัก**: `src/`, `public/`, `resources/`, etc.

## 6. 📏 ตรวจสอบขนาด (Size Verification)
- ตรวจสอบขนาดรวมของโฟลเดอร์ต้นทางและปลายทางให้เท่ากัน (หรือไม่ต่างกันมากกว่า 5%)
```cmd
powershell -Command "(Get-ChildItem -Recurse -File 'c:\\laragon\\www\\hr_budget' | Measure-Object -Property Length -Sum).Sum"
powershell -Command "(Get-ChildItem -Recurse -File 'c:\\laragon\\www\\hr_budget\\archives\\test' | Measure-Object -Property Length -Sum).Sum"
```
- หากขนาดไม่ตรง ให้ตรวจสอบไฟล์ที่หายหรือคัดลอกใหม่

## 7. 🔙 Rollback (ถ้าตรวจสอบล้มเหลว)
- หากขั้นตอน Verification หรือ Size Verification ล้มเหลว ให้คืนไฟล์จาก backup
```cmd
robocopy "c:\\laragon\\www\\hr_budget\\archives\\backup\\last_successful" "c:\\laragon\\www\\hr_budget" /E
```
- บันทึกเหตุผลการ Rollback ลงใน `project-log-md/organize-log.md`

## ✅ Checklist สำหรับ Agent
- [ ] 0. **Pre-Check**: ตรวจสอบ cwd และสร้าง Log เริ่มต้น
- [ ] 1. อ่านและจำแนกประเภทไฟล์
- [ ] 2. สร้างรายการไฟล์ที่จะย้าย แสดงให้ User เห็น
- [ ] 3. รอ User Confirm (ถ้าไม่แน่ใจ)
- [ ] 4. รันคำสั่งย้ายไฟล์
- [ ] 5. **[MANDATORY]** Run `list_dir` ตรวจสอบปลายทาง
- [ ] 6. **[MANDATORY]** Run `view_file` ตรวจสอบ 1-2 ไฟล์ว่าเปิดได้
- [ ] 7. ตรวจสอบต้นทางว่าไฟล์หายไปแล้ว (ถ้า Move)
- [ ] 8. **[MANDATORY]** Run Size Verification
- [ ] 9. บันทึก Log สรุปผลการย้าย → `project-log-md/organize-log.md`
- [ ] 10. แจ้ง User พร้อมสรุปผล (หรือ Rollback หากล้มเหลว)

