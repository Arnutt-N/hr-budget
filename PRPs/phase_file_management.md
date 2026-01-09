# Implementation Plan: File Management Phase

> **Created**: 2025-12-17 08:12:00
> **Phase**: New Phase (Between 3 & 4)
> **Priority**: P1

---

## Goal
สร้างระบบ**คลังเอกสาร (Document Archive)** จัดเก็บตามโครงสร้างงบประมาณ โดยสามารถ:
- ท่องไฟล์ตามปีงบประมาณและหมวดหมู่งบประมาณ
- สร้างโฟลเดอร์อัตโนมัติจากหมวดหมู่ Level 0 (งบบุคลากร, งบดำเนินงาน, ฯลฯ)
- สร้างโฟลเดอร์ย่อยได้ไม่จำกัดระดับ
- อัปโหลด/ดาวน์โหลดไฟล์ (PDF, Excel, Word, รูปภาพ)
- ลบไฟล์และโฟลเดอร์ที่สร้างเอง (ไม่สามารถลบโฟลเดอร์ระบบ)

---

## User Review Required

> [!NOTE]
> **ระบบนี้ใช้ Local Storage** (`public/uploads/`) สำหรับ Self-hosted Server
> สามารถ Migrate ไป Cloud Storage (S3/GCS) ได้ในอนาคตถ้าต้องการ

---

## Proposed Changes

### A. Database Tables

#### [NEW] `folders` - โฟลเดอร์จัดเก็บตามโครงสร้างงบประมาณ
```sql
CREATE TABLE folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    fiscal_year INT NULL COMMENT 'ปีงบประมาณ (2568, 2569, ...)',
    budget_category_id INT NULL COMMENT 'เชื่อมกับหมวดหมู่งบประมาณ (ถ้ามี)',
    parent_id INT NULL COMMENT 'โฟลเดอร์แม่ (สำหรับโฟลเดอร์ที่สร้างเอง)',
    folder_path VARCHAR(500) NULL COMMENT 'เส้นทางเต็มของโฟลเดอร์',
    description TEXT NULL,
    is_system TINYINT(1) DEFAULT 0 COMMENT '1 = สร้างจากระบบ, 0 = สร้างเอง',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_fiscal_year (fiscal_year),
    INDEX idx_category (budget_category_id)
);
```

#### [NEW] `files` - เอกสารในโฟลเดอร์
```sql
CREATE TABLE files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    folder_id INT NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50) NOT NULL COMMENT 'pdf, xlsx, png, etc.',
    file_size INT NOT NULL COMMENT 'Size in bytes',
    mime_type VARCHAR(100) NULL,
    description TEXT NULL,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_folder (folder_id)
);
```

---

### B. File Storage Structure
```
public/uploads/
├── 2568/                                   # ปีงบประมาณ
│   ├── งบบุคลากร/                          # หมวดหมู่ Level 0 (สร้างอัตโนมัติ)
│   │   ├── [โฟลเดอร์สร้างเอง]/             # สร้างเองได้ไม่จำกัดระดับ
│   │   │   ├── [โฟลเดอร์ย่อย]/
│   │   │   │   └── ไฟล์...
│   │   │   └── ไฟล์...
│   │   └── ไฟล์...
│   ├── งบดำเนินงาน/                        # หมวดหมู่ Level 0 อื่นๆ
│   │   └── ...
│   └── งบลงทุน/
├── 2569/
└── temp/
```

---

### C. Models

#### [NEW] `src/Models/Folder.php`
- `getRootFolders($fiscalYear)`: ดึงโฟลเดอร์ระดับบนสุดของปีงบประมาณ
- `getSubfolders($parentId)`: ดึงโฟลเดอร์ย่อย
- `getTree($fiscalYear)`: ดึงแบบ Tree Structure
- `getBreadcrumb($id)`: ดึงเส้นทาง (breadcrumb)
- `create($data)`: สร้างโฟลเดอร์
- `delete($id)`: ลบโฟลเดอร์ (ไม่ได้ถ้าเป็น system folder)
- `initializeForYear($fiscalYear, $createdBy)`: สร้างโฟลเดอร์ตามหมวดงบประมาณ

#### [NEW] `src/Models/File.php`
- `upload($file, $folderId, $uploadedBy, $description)`: อัปโหลดไฟล์
- `getByFolder($folderId)`: ดึงไฟล์ในโฟลเดอร์
- `find($id)`: ค้นหาไฟล์
- `delete($id)`: ลบไฟล์ (ทั้ง DB และ Disk)
- `getIcon($type)`: ดึง icon ตามประเภทไฟล์
- `formatSize($bytes)`: แปลงขนาดไฟล์เป็น KB/MB

---

### D. Controller & Routes

#### [NEW] `src/Controllers/FileController.php`
| Method | Route | Description |
|--------|-------|-------------|
| `index()` | GET `/files?year={year}&folder={id}` | หน้าคลังเอกสาร (เลือกปี/โฟลเดอร์) |
| `upload()` | POST `/files/upload` | อัปโหลดไฟล์ |
| `download($id)` | GET `/files/{id}/download` | ดาวน์โหลดไฟล์ |
| `deleteFile($id)` | POST `/files/{id}/delete` | ลบไฟล์ |
| `createFolder()` | POST `/folders` | สร้างโฟลเดอร์ย่อย |
| `deleteFolder($id)` | POST `/folders/{id}/delete` | ลบโฟลเดอร์ |
| `initializeYear()` | POST `/files/init` | สร้างโฟลเดอร์ตามหมวดงบประมาณ |

---

### E. UI Design

#### หน้าคลังเอกสาร (Document Archive)
```
+-------------------------------------------------------------+
|  📁 คลังเอกสาร         [ปี: 2568 ▼]  [+ โฟลเดอร์] [⬆ อัปโหลด] |
+-------------------------------------------------------------+
| Breadcrumb: ปี 2568 / งบบุคลากร / โฟลเดอร์ย่อย              |
+-------------------------------------------------------------+
|                                                             |
|  📂 โฟลเดอร์ซ้าย (Tree)     │  📄 ไฟล์ในโฟลเดอร์ปัจจุบัน |
|  ├── 📁 งบบุคลากร (L0)      │  ┌────────────────────────┐ |
|  │   ├── 📁 โฟลเดอร์ย่อย     │  │ 📄 รายงาน_Q1.pdf       │ |
|  ├── 📁 งบดำเนินงาน (L0)    │  │ 📊 งบเบิกจ่าย.xlsx     │ |
|  └── 📁 งบลงทุน (L0)        │  │ 🖼 หลักฐาน.png         │ |
|                             │  └────────────────────────┘ |
+-------------------------------------------------------------+
```

---

## Verification Plan

### Manual Verification
1. ✅ เลือกปีงบประมาณได้
2. ✅ กดปุ่ม "สร้างโฟลเดอร์ตามหมวดงบประมาณ" สร้างโฟลเดอร์ L0 ได้
3. ✅ เข้าโฟลเดอร์ L0 และสร้างโฟลเดอร์ย่อยได้
4. ✅ อัปโหลดไฟล์ PDF/Excel/Word/รูปภาพได้
5. ✅ ดาวน์โหลดไฟล์ได้
6. ✅ ลบไฟล์ได้
7. ✅ ลบโฟลเดอร์ที่สร้างเองได้ (โฟลเดอร์ระบบลบไม่ได้)
8. ✅ Sidebar แสดง Folder Tree ถูกต้อง

### Database Verification
```sql
SELECT COUNT(*) FROM files;
SELECT COUNT(*) FROM folders;
```
