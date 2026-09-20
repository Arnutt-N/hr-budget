# PRD: แก้ไขผลการ Design Review SPA (M1–M12 + minors)

วันที่: 2026-09-13
สถานะ: รอ approve
ที่มา: design-review ครบ 31 routes (คะแนนรวม 6.0/10) + live pass (Playwright + axe + real-render contrast + 360px overflow + focus sampling โหมด mock API)

## Baseline และเป้าหมาย

คะแนนปัจจุบัน: Hierarchy 7 / Consistency 6 / Accessibility 5 / Usability 6 / Responsiveness 5 / Performance 7
เป้าหมายหลังแก้: **รวม ≥ 8.0** โดย Accessibility ≥ 7 และ Responsiveness ≥ 7 (สองมิติที่ live pass ยืนยันว่าตกจริง)

หลักฐาน live (reproduce ได้ด้วย `npm run test:e2e:audit`): axe serious/critical = link-name 6 (mock artifact — ตัดออก), color-contrast 4, label 4, target-size 1; contrast engine พบปุ่ม primary 4.10:1 จำนวน 5 จุด; overflow360 > 0 จำนวน 26 หน้า (23–563px); interaction probe ยืนยัน M6/M11

## Requirements

### R1 — Contrast (M1, M12)
- R1.1 ปุ่มพื้น `primary-600` ตัวอักษรขาวต้องได้ ≥ 4.5:1 (ปัจจุบัน 4.10) — เปลี่ยนค่า token `primary-600: #0284c7 → #0369a1` (วัดแล้ว 5.93:1) ครอบคลุมปุ่ม native, stepper pill, avatar
- R1.2 hover `primary-500` ตัวอักษรขาว (ปัจจุบัน 2.77:1) ต้องไม่ต่ำกว่า 4.5:1 — ชี้ hover ไปสีที่เข้มพอ
- R1.3 PrimeVue info Message (ปัจจุบัน 3.24:1) ต้อง ≥ 4.5:1 — override สีข้อความเป็น `primary-400`
- R1.4 ไอคอนขาวบนวงกลม `primary-500` ที่หน้า login (ปัจจุบัน 2.77:1, เกณฑ์ non-text 3.0) ต้องผ่าน

### R2 — A11y markup P0 (M2, M8, M9, M10)
- R2.1 ItemEditor: input ทุกช่องมีชื่อ (axe `label` = 0) — `aria-label` ระบุแถว+คอลัมน์; ปุ่มลบรายแถวระบุชื่อแถว
- R2.2 Phase 9: controls ไร้ label ~30 จุด (dialog Policy/Vacancy/Allowance/Assignment, ฟอร์ม rates + flags ใน AllowanceType, ฟิลเตอร์ Position, ToggleSwitch รอบเงินเดือน) ต้องมี label ครบ — ใช้ `FormField` idiom เดิม
- R2.3 มี skip link ไป `#main` (WCAG 2.4.1) บน AppLayout
- R2.4 ปุ่ม Export Excel ต้องไม่ซ้อน button ใน link — เหลือ anchor เดียวที่ style เป็นปุ่ม

### R3 — Responsive (M4)
- R3.1 ตารางทั้ง 23 จุด + ตาราง items ใน RequestDetail ต้อง scroll แนวนอนใน card ได้ (ไม่บีบ/ตัดเนื้อหา) — shared wrapper ตัวเดียว; ลบ prop `responsive-layout` ที่ตายแล้ว
- R3.2 `overflow360 = 0` ทุก route — รวมกลไก shell-stretch (AppLayout root 816px @360, พบที่ notifications) ผ่าน min-w-0 audit + `overflow-x: clip` ที่ shell
- R3.3 PageHeader ต้อง wrap บนจอแคบ (ปุ่ม action ถูกตัดที่ 360px)

### R4 — Usability / errors (M5, M6, M11 + minors)
- R4.1 รวม error states เป็น `QueryErrorState` + ปุ่ม Retry ทุกหน้า (เลิก rose div / red-500/10 ที่ประกาศบ้างไม่บ้าง)
- R4.2 dirty guard: wizard Cancel, RequestEdit ยกเลิก ต้อง confirm เมื่อมีงานที่ยังไม่บันทึก
- R4.3 dialog Allowance/Assignment: บันทึกทั้งที่ฟิลด์ขาดต้องมี feedback (toast หรือ inline) — ห้ามเงียบ; หน้าอื่นที่ disable ปุ่มต้องมี hint เหตุผล
- R4.4 ฟอร์ม RequestCreate อยู่ใน `<form>` (Enter ส่งได้); ฟิลด์ cross-validate (SalaryScale max<min) แสดง error ที่ฟิลด์ ไม่ใช่แค่ toast
- R4.5 สถานะหลัง mutation ต้องประกาศกับ screen reader (approve/submit badge, rejected_reason) — live region
- R4.6 confirm ลบทุกจุดต้องระบุตัวตนรายการ (ชื่อ/เลข) — ห้ามข้อความ generic ล้วน

### R5 — Charts (M3)
- R5.1 canvas กราฟทั้ง 5 ตัวต้องมี `role="img"` + `aria-label` สรุปข้อมูล; dashboard + analytics ต้องมีตารางข้อมูลแบบซ่อนให้ screen reader (execution มีตารางอยู่แล้ว)

### R6 — Minors (เก็บในรอบเดียวกัน)
- M7: remap เฉดที่ไม่มีในธีม (`primary-200/300/900`, 4 ไฟล์) → เฉดที่มีอยู่; เป้ากดข้อความล้วน ≥ 24px + aria-label รายแถว (รวม breadcrumb vault 20px + `aria-current`); decorative icons `aria-hidden`; `<p>` ใน `<button>` → span; `color-scheme: dark` ใน base CSS; drawer โฟกัสเข้า/กับดักโฟกัส; analytics request-tab empty state + h2 ประจำ chart section; role ภาษาไทย + ชื่อ product ตรงกัน; favicon path ใต้ subdirectory; ปัด copy (em-dash/jargon/`'1-12'`); ตาราง compute แสดงชื่อหน่วยงานแทน id; edit-guard redirect ต้องมี toast; Role toggle บน system role ต้องมีคำอธิบาย

## Non-goals
- การตัดสินใจ DTCG `tokens/*.json` (adopt-or-remove) — เป็น strategic call แยก (ดูคำถามเปิด)
- Keyboard shortcuts / bulk actions, ระบบ contextual help, งาน backend (ไม่ต้องแตะ), redesign ภาพรวม

## Acceptance ระดับ release
- `npm run test:e2e:audit`: axe serious/critical = 0, contrast fails = 0, overflow360 = 0 ทุก route, ไม่มี pageerror นอกเหนือ mock artifacts ที่บันทึกไว้
- `composer verify` + `cd frontend && npm run verify` เขียว
- รีวิว 360px screenshots ทุก route ด้วยตา + keyboard walkthrough (Tab/Enter/Escape) ผ่านโฟลว์หลักโดยไม่ใช้เมาส์

## คำถามเปิด
- DTCG pack: adopt (wire เข้า SPA) หรือ remove ออกจาก scope lint? (ต้องการเจ้าของตัดสินใจ — ไม่ขวางงานรอบนี้)
