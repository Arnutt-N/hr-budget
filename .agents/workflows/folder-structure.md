---
description: โครงสร้างโฟลเดอร์และกติกาการจัดเก็บไฟล์
---

# Project Folder Structure & Conventions

## 📁 โครงสร้างโฟลเดอร์หลัก

```
hr_budget/
├── research/          # วิเคราะห์ วิจัย (local-only, git-ignored — ไม่อยู่ใน repo)
├── PRPs/              # สร้างแผน จากเอกสารวิจัย ก่อนลงมือทำ
├── examples/          # ตัวอย่างไฟล์อ้างอิง, UI design, references
├── project-log-md/    # เก็บล็อกจากการทำงาน
├── python/            # เก็บไฟล์ python , venv tasks and logs ที่ใช้ python
├── scripts/           # เก็บ scripts สำหรับรันต่างๆ
├── docs/              # เก็บ เอกสารโปรเจค  specification, requirements, user 
|                        stories README, คู่มือการใช้งาน, API documentation
├── archives/          # ไฟล์/โฟลเดอร์ที่ไม่ใช้แล้ว
│   ├── data/
│   ├── backup/
│   ├── test/
│   └── unused/
└── [project files]    # ไฟล์ source code หลัก
```

## 📝 รายละเอียดแต่ละโฟลเดอร์

### `research/` (local-only, git-ignored)
- เอกสารวิเคราะห์และวิจัย — อยู่เฉพาะเครื่องผู้ใช้ ไม่ถูก track ใน repo
- Database schema documentation
- Requirements analysis
- Technical research
- ผลงานที่ต้องส่งต่อ ให้ย้ายไป `PRPs/` หรือ `docs/` ก่อนถึงจะอยู่ใน repo ได้

### `PRPs/` (Project Request Proposals)
- แผนงานที่สร้างจากเอกสารวิจัย
- Implementation plans
- Design proposals
- **ต้องสร้างก่อนลงมือทำงาน**

### `examples/`
- ตัวอย่างไฟล์อ้างอิง
- UI/UX design mockups
- Reference code snippets
- External resources

### `project-log-md/`
- Log จากการทำงาน
- Task checklists
- Walkthrough documents
- Session summaries
- Screenshots

### `python/`
- Files จาก python
- Log จากการทำงาน จาก python
- Task checklists จาก python
- Walkthrough documents จาก python
- Session summaries จาก python
- Screenshots จาก python

### `scripts/`
- Shell scripts
- Migration scripts
- Build scripts
- Utility scripts

### `docs/` (Project Documents)
- specification, requirements 
- user stories
- README
- คู่มือการใช้งาน
- API documentation

### `archives/`
- ไฟล์ที่ไม่ใช้งานแล้ว
- **Sub-folders:**
  - `data/` - ข้อมูลเก่า
  - `backup/` - สำรองข้อมูล
  - `test/` - ไฟล์ทดสอบ
  - `unused/` - code ที่ไม่ใช้แล้ว

## ✅ กติกาการทำงาน

1. **ก่อนลงมือ**: สร้างแผนใน `PRPs/` จากการวิจัยใน `research/`
2. **ระหว่างทำ**: บันทึก log ใน `project-log-md/`
3. **ไม่ใช้แล้ว**: ย้ายไป `archives/` (ไม่ลบทิ้ง)
4. **อ้างอิง**: เก็บตัวอย่างใน `examples/`