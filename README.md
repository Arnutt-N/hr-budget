# HR Budget Project

โปรเจค HR Budget - ระบบจัดการงบประมาณทรัพยากรบุคคล

## 📁 โครงสร้างโปรเจค

```
hr_budget/
├── .agents/workflows/    # AI workflows สำหรับการทำงานร่วมกับ AI
├── archives/            # ไฟล์เก่าที่ไม่ใช้งานแล้ว
│   ├── backup/          # สำรองไฟล์สำคัญ
│   ├── data/            # ข้อมูลเก่า
│   ├── test/            # ไฟล์ทดสอบเก่า
│   └── unused/          # ไฟล์/โค้ดที่ไม่ใช้แล้ว
├── assets/              # ไฟล์ static (รูปภาพ, ฟอนต์, ไอคอน)
│   ├── images/          # รูปภาพ
│   ├── fonts/           # ฟอนต์
│   └── icons/           # ไอคอน
├── config/              # ไฟล์ configuration
├── docs/                # เอกสารโปรเจค
├── examples/            # ตัวอย่างอ้างอิง, UI references
├── PRPs/                # แผนงานก่อนลงมือทำ (Pre-work Plans)
├── project-log-md/      # บันทึกการทำงาน
├── research/            # วิจัย วิเคราะห์
├── scripts/             # Scripts สำหรับรันงานต่างๆ
└── src/                 # Source code หลัก
```

## 🏗️ สถาปัตยกรรม (Architecture)

ตั้งแต่ Phase 6 cutover (2026-06-15) ระบบมี frontend เดียว:

- **Frontend** = Vue 3 SPA ใน `frontend/` (PrimeVue + TanStack Query, auth ผ่าน JWT cookie)
  - dev: `cd frontend && npm run dev` (Vite dev server ที่ `:5174`)
  - build (CI/default): `cd frontend && npm run build` → `frontend/dist` (base `/`)
  - build (deploy): `cd frontend && VITE_BASE=/hr_budget/public/app/ npm run build` → `public/app/` (tracked, เสิร์ฟโดย PHP)
- **Backend** = PHP 8.3 custom MVC เปิดเฉพาะ **`/api/v1/*`** (JSON API) + เสิร์ฟ SPA shell
  (`public/app/index.html`) ผ่าน catch-all ใน `Router::notFound()` สำหรับทุก path ที่ไม่ใช่ API
- **Legacy web remnants** ที่ยังคงไว้ (ยังไม่มีหน้า SPA แทน): ThaID login (`/thaid/login`),
  รายงานการเบิกจ่าย (`/budgets`, `/budgets/export`), document vault (`/files`, `/folders`)
- หน้าเว็บ/คอนโทรลเลอร์เดิมที่ถูกปลดระวางกู้คืนได้จาก git tag `pre-spa-cutover`

## 🚀 เริ่มต้นใช้งาน

<!-- เพิ่มคำแนะนำการติดตั้งและใช้งานที่นี่ -->

## 📝 License

<!-- เพิ่ม license ที่นี่ -->
