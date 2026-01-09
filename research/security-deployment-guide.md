# 🔒 คู่มือความปลอดภัยในการ Deploy โปรเจค HR Budget

> **วันที่:** 2025-12-15  
> **โปรเจค:** HR Budget Management System  
> **Server:** https://topzlab.com/hr-budget/

---

## ⚠️ ไฟล์ที่ห้ามอัปโหลดขึ้น Server (CRITICAL)

### 🔴 ไฟล์ที่มีข้อมูลลับ (Secrets)

| ไฟล์/โฟลเดอร์ | เหตุผล | ระดับความเสี่ยง |
|--------------|--------|----------------|
| `.env` | มี Database Password, API Keys | 🔴 **สูงมาก** |
| `config/database.php` | อาจมี hardcoded credentials | 🔴 **สูงมาก** |
| `.env.local` | ไฟล์ config สำหรับ local | 🔴 **สูงมาก** |

> **หมายเหตุ:** ไฟล์ `.env` ต้อง**สร้างใหม่บน Server** แยกต่างหาก ไม่ควรคัดลอกจาก local เพราะมี credentials ที่ต่างกัน

---

### 🟠 ไฟล์/โฟลเดอร์ Debug และ Development

| ไฟล์/โฟลเดอร์ | เหตุผล | ระดับความเสี่ยง |
|--------------|--------|----------------|
| `scripts/` | มี debug scripts, database dumps | 🟠 **สูง** |
| `debug_items.php` | ไฟล์ debug | 🟠 **สูง** |
| `dump_items.php` | ไฟล์ debug | 🟠 **สูง** |
| `list_duplicates.php` | ไฟล์ debug | 🟠 **สูง** |
| `simple_test.php` | ไฟล์ test | 🟠 **สูง** |
| `public/debug_*.php` | Debug endpoints | 🟠 **สูง** |
| `public/audit_schema_http.php` | แสดง schema ของ DB | 🟠 **สูง** |
| `public/fix_trackings.php` | migration script | 🟠 **สูง** |
| `public/run_backup.php` | ทำ backup ได้ | 🟠 **สูง** |
| `public/db-api.php` | มี token ที่ hardcoded | 🟠 **สูง** |

---

### 🟡 ไฟล์/โฟลเดอร์ที่ไม่จำเป็นบน Production

| ไฟล์/โฟลเดอร์ | เหตุผล | ระดับความเสี่ยง |
|--------------|--------|----------------|
| `node_modules/` | ไฟล์ขนาดใหญ่, ไม่จำเป็นสำหรับ PHP | 🟡 กลาง |
| `tests/` | Unit tests | 🟡 กลาง |
| `test-results/` | ผลลัพธ์การ test | 🟡 กลาง |
| `playwright-report/` | Browser test reports | 🟡 กลาง |
| `.agent/` | Agent workflows | 🟡 กลาง |
| `PRPs/` | Planning documents | 🟡 กลาง |
| `research/` | Research documents | 🟡 กลาง |
| `project-log-md/` | Project logs | 🟡 กลาง |
| `archives/` | Backup files | 🟡 กลาง |
| `docs/` | Documentation | 🟡 กลาง |
| `examples/` | Example files | 🟡 กลาง |
| `*.bat` | Windows batch files | 🟡 กลาง |
| `phpunit.xml` | PHPUnit config | 🟡 กลาง |
| `playwright.config.ts` | Playwright config | 🟡 กลาง |
| `vite.config.js` | Vite config (dev only) | 🟡 กลาง |
| `package.json` | Node.js config | 🟡 กลาง |
| `package-lock.json` | Node.js lock file | 🟡 กลาง |
| `composer.lock` | อาจเก็บได้แต่ไม่จำเป็น | 🟢 ต่ำ |

---

## ✅ ไฟล์/โฟลเดอร์ที่ต้องอัปโหลด

### โครงสร้างที่ควรอัปโหลด

```
hr-budget/
├── index.php              ✅ (ไฟล์ entry point ใหม่)
├── .htaccess              ✅ (มี HTTPS redirect)
├── composer.json          ✅
├── vendor/                ✅ (dependencies)
├── config/                ✅ (ยกเว้น credentials)
├── database/              ✅ (migrations)
├── public/                ✅ (ดูรายละเอียดด้านล่าง)
├── resources/             ✅ (views, assets)
├── routes/                ✅
├── src/                   ✅ (Controllers, Models, Core)
└── storage/               ✅ (ต้องสร้างให้ writable)
```

### โฟลเดอร์ `public/` - ต้องกรองไฟล์

| ไฟล์ | สถานะ |
|------|-------|
| `index.php` | ✅ อัปโหลด |
| `.htaccess` | ✅ อัปโหลด |
| `assets/` | ✅ อัปโหลด |
| `css/` | ✅ อัปโหลด |
| `js/` | ✅ อัปโหลด |
| `images/` | ✅ อัปโหลด |
| `debug_*.php` | ❌ **ห้ามอัปโหลด** |
| `db-api.php` | ❌ **ห้ามอัปโหลด** |
| `fix_trackings.php` | ❌ **ห้ามอัปโหลด** |
| `run_backup.php` | ❌ **ห้ามอัปโหลด** |
| `audit_schema_http.php` | ❌ **ห้ามอัปโหลด** |

---

## 🛡️ การตั้งค่าความปลอดภัยบน Server

### 1. ไฟล์ `.env` บน Production
สร้างไฟล์ `.env` ใหม่บน Server:

```env
APP_NAME="HR Budget System"
APP_ENV=production
APP_DEBUG=false                    # ❗ ต้องปิด debug
APP_URL=https://topzlab.com/hr-budget

SESSION_SECURE=true                # ❗ บังคับ HTTPS session

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_production_db     # ❗ ใช้ค่าของ production
DB_USERNAME=your_production_user
DB_PASSWORD=your_production_pass
```

### 2. Permission ที่ถูกต้อง

```bash
# โฟลเดอร์: 755
find /hr-budget -type d -exec chmod 755 {} \;

# ไฟล์: 644
find /hr-budget -type f -exec chmod 644 {} \;

# Storage ต้องเขียนได้: 775
chmod -R 775 /hr-budget/storage

# .env ต้องอ่านได้เฉพาะ owner: 600
chmod 600 /hr-budget/.env
```

### 3. ป้องกันการเข้าถึงไฟล์สำคัญ
เพิ่มใน `.htaccess`:

```apache
# ป้องกันการเข้าถึง .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>

# ป้องกันการเข้าถึง composer files
<Files composer.*>
    Order allow,deny
    Deny from all
</Files>
```

---

## 📋 Checklist ก่อน Deploy

- [ ] ลบไฟล์ debug ทั้งหมดออกจาก `public/`
- [ ] ลบโฟลเดอร์ `scripts/`, `tests/`, `node_modules/`
- [ ] ตรวจสอบว่าไม่มี hardcoded credentials ใน code
- [ ] สร้างไฟล์ `.env` ใหม่บน Server
- [ ] ตั้งค่า `APP_DEBUG=false`
- [ ] ตั้งค่า `SESSION_SECURE=true`
- [ ] ตั้ง Permission ให้ถูกต้อง (755/644)
- [ ] ป้องกัน `.env` ด้วย `.htaccess`
- [ ] ทดสอบ HTTPS redirect ทำงาน
- [ ] ลบ/ซ่อน Error messages ที่แสดงข้อมูลละเอียด

---

## 🔍 ไฟล์ที่ต้องตรวจสอบเป็นพิเศษ

### `config/database.php`
ตรวจสอบว่าใช้ `$_ENV` หรือ `getenv()` ไม่มี hardcoded password:

```php
// ✅ ถูกต้อง
'password' => $_ENV['DB_PASSWORD'] ?? '',

// ❌ ผิด - ห้ามทำ!
'password' => 'my_secret_password',
```

### `public/db-api.php`
ไฟล์นี้มี token hardcoded:
```php
$token = 'debug_2024_hr_budget_secure';  // ❌ อันตราย!
```
**ต้องลบไฟล์นี้ออกจาก Production**

---

## 📦 สรุปคำสั่งลบไฟล์ที่ไม่ต้องการ (บน Local ก่อนอัปโหลด)

```bash
# ลบไฟล์ debug
del public\debug_*.php
del public\db-api.php
del public\fix_trackings.php
del public\run_backup.php
del public\audit_schema_http.php

# ลบไฟล์ debug ที่ root
del debug_items.php
del dump_items.php
del list_duplicates.php
del simple_test.php

# ลบโฟลเดอร์ที่ไม่จำเป็น
rmdir /s /q node_modules
rmdir /s /q tests
rmdir /s /q test-results
rmdir /s /q playwright-report
rmdir /s /q scripts
```

---

> **⚠️ คำเตือน:** อย่าอัปโหลดไฟล์ `.env` จาก local ไป production โดยเด็ดขาด!  
> ต้องสร้างไฟล์ `.env` ใหม่บน Server ด้วย credentials ของ production เท่านั้น

---

## 🗄️ Database Migrations

### การ Apply Migrations บน Production

โปรเจคมี migrations ทั้งหมด 5 ไฟล์ใน `database/migrations/`:

```bash
# 1. สร้างตาราง personnel_types
mysql -u [user] -p [database] < database/migrations/001_create_personnel_types.sql

# 2. สร้างตาราง files
mysql -u [user] -p [database] < database/migrations/002_create_files.sql

# 3. แก้ไขตาราง users (เพิ่มฟิลด์ใหม่)
mysql -u [user] -p [database] < database/migrations/003_alter_users.sql

# 4. สร้างตาราง fiscal_years
mysql -u [user] -p [database] < database/migrations/004_create_fiscal_years.sql

# 5. สร้างตาราง budget_records
mysql -u [user] -p [database] < database/migrations/007_create_budget_records.sql
```

### Migrations Checklist

- [ ] Apply 001_create_personnel_types.sql
- [ ] Apply 002_create_files.sql
- [ ] Apply 003_alter_users.sql
- [ ] Apply 004_create_fiscal_years.sql
- [ ] Apply 007_create_budget_records.sql
- [ ] ตรวจสอบ foreign keys ทำงานถูกต้อง
- [ ] Verify ว่า `budget_trackings` มี unique index (`unique_tracking`)

---

## 📊 Models ที่เพิ่มใหม่

### `BudgetTracking.php`
- จัดการข้อมูล `budget_trackings` table
- Methods: `getByFiscalYear()`, `upsert()`, `getSummary()`, `delete()`
- รองรับ bulk operations

### `BudgetRecord.php`
- จัดการข้อมูล `budget_records` table  
- บันทึกข้อมูลรายเดือน (โอนจัดสรร, ขออนุมัติ, PO)

---

## 🧪 Testing

ระบบมี Unit Tests ใน `tests/Unit/Models/`:
- `BudgetRequestTest.php`
- `BudgetRequestItemTest.php`
- `BudgetTrackingTest.php` (ใหม่)

**รัน Tests ก่อน Deploy:**
```bash
php vendor/bin/phpunit --testsuite=Unit
```

> **หมายเหตุ:** Tests ต้องการ database `hr_budget_test`

