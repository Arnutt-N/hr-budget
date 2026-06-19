# PRP: หน้ารายงานวิเคราะห์ (Analytics) — เปรียบเทียบ / Forecast vs จริง / คำขอ vs อนุมัติ

**วันที่:** 2026-06-19
**สถานะ:** Design (รออนุมัติก่อน implement)
**ขอบเขต:** 3 ฟีเจอร์วิเคราะห์ รวมในหน้าใหม่ `/analytics` ("รายงานวิเคราะห์")

---

## 1. เป้าหมาย

เพิ่มหน้า **รายงานวิเคราะห์** หนึ่งหน้า (3 ส่วน) บน SPA โดย **ไม่แก้ schema** (ใช้ตารางเดิมทั้งหมด ตามที่ตกลง: forecast = allocated ÷ 12):

| # | ฟีเจอร์ | แหล่งข้อมูล (ตารางเดิม) |
|---|---------|------------------------|
| A | เปรียบเทียบ รายปี / ไตรมาส / เดือน | `budget_trackings` (+ disbursement chain, `budget_transactions`) |
| B | Forecast vs เบิกจ่ายจริง | forecast = `SUM(allocated)/12`; actual = disbursed รายเดือน |
| C | คำขอ vs อนุมัติ (ตาม พรบ) ฟิลเตอร์รายปี | `budget_requests` (`total_amount`, `request_status`) |

หลักการ: **อ่านอย่างเดียว (read-only aggregation)** เหมือน `DashboardService`/`BudgetExecutionService` — SQL driver-portable (ไม่ใช้ฟังก์ชัน MySQL-only) เพื่อ unit-test กับ SQLite ได้

---

## 2. Data model (ตารางเดิม ไม่มี migration)

### A. เปรียบเทียบ
- **รายปี:** `SELECT fiscal_year, SUM(allocated+transfer) budget, SUM(disbursed) disbursed FROM budget_trackings GROUP BY fiscal_year` → เทียบ 2568/2569/2570
- **รายไตรมาส:** เหมือน `BudgetExecutionRepository` (chain → `ds.record_month` แบ่ง Q1–Q4) ของปีที่เลือก
- **รายเดือน:** เหมือน `DashboardService::monthlyExpenditure` (12 bucket ต.ค.→ก.ย.) ของปีที่เลือก

### B. Forecast vs Actual (ปีที่เลือก)
- **Forecast รายเดือน** = `SUM(allocated WHERE fiscal_year)/12` (เส้นแบน 12 เดือน) — ตามที่ตกลง
- **Actual รายเดือน** = disbursed แต่ละเดือน (จาก `budget_transactions` expenditure ตามหน้าต่างปีงบ)
- เพิ่ม **เส้นสะสม (cumulative)** ทั้ง forecast/actual เพื่อดู gap

### C. คำขอ vs อนุมัติ (ฟิลเตอร์ `fiscal_year`)
- **ยอดขอ** = `SUM(total_amount) FROM budget_requests WHERE fiscal_year=?`
- **ยอดอนุมัติ (ตาม พรบ)** = `SUM(total_amount) WHERE fiscal_year=? AND request_status='approved'`
- **อัตราอนุมัติ** = อนุมัติ ÷ ขอ; แยกตามสถานะ + ตามหน่วยงาน (`org_id` JOIN `organizations`)

---

## 3. Backend (layered: Controller → Service → Repository → DTO)

ไฟล์ใหม่ (ตามสไตล์ API เดิม):
- `src/Repositories/AnalyticsRepository.php` — 3 กลุ่ม query ข้างบน (SQL portable)
- `src/Services/AnalyticsService.php` — คำนวณ forecast/อัตรา/สะสม, จัด label ไทย
- `src/Api/Controllers/AnalyticsController.php`
- `src/Dtos/Analytics*.php` — response shapes
- `routes/web.php` (block `/api/v1/*`):
  - `GET /api/v1/analytics/comparison?fiscal_year=&dimension=year|quarter|month`
  - `GET /api/v1/analytics/forecast?fiscal_year=`
  - `GET /api/v1/analytics/request-vs-approved?fiscal_year=`
- ทุก response ใช้ `ApiResponse` envelope; ผ่าน `AuthMiddleware`
- **Tests:** `tests/Unit/Services/AnalyticsServiceTest.php` (SQLite fixture เหมือน `DashboardService`/RBAC tests)

---

## 4. Frontend (Vue 3 + chart.js/vue-chartjs)

- `src/api/analytics.ts` (ใช้ `apiUrl()` — subdir-safe)
- `src/queries/useAnalytics.ts` (TanStack Query: `useComparison`, `useForecast`, `useRequestApproval`)
- `src/pages/AnalyticsPage.vue` — 3 ส่วน + ตัวเลือกปีงบ (reuse pattern จาก `BudgetExecutionPage`)
- Components ใหม่ (reuse chart.js เดิม):
  - `ComparisonChart.vue` — toggle ปี/ไตรมาส/เดือน → grouped Bar (จัดสรร vs เบิกจ่าย)
  - `ForecastChart.vue` — Line (forecast เส้นประ vs actual เส้นทึบ; toggle รายเดือน/สะสม)
  - `RequestApprovalChart.vue` — KPI cards (ขอ/อนุมัติ/อัตรา) + Bar ตามหน่วยงาน + แยกสถานะ
- `src/router/index.ts` — เพิ่ม route `analytics` (lazy import)
- เพิ่มเมนู "รายงานวิเคราะห์" ใน sidebar (กลุ่มเดียวกับ ผลการเบิกจ่าย)

---

## 5. Demo data (เพิ่มหลายปีให้เทียบได้)

ขยาย `hr_budget_demo_data.sql`:
- **2568** (ปิดปีแล้ว): budget_trackings + chain เบิกจ่ายเกือบเต็ม (~95%)
- **2570** (ปีใหม่): budget_trackings จัดสรรแล้ว เบิกจ่ายน้อย (~10%)
- budget_requests ปี 2568/2570 (อัตราอนุมัติต่างกัน)
→ กราฟเปรียบเทียบรายปีเห็น 3 ปี, request-vs-approved ฟิลเตอร์ได้หลายปี

---

## 6. แผน implement (เป็นเฟส, TDD)

1. **Backend** — Repository+Service+Controller+DTO+routes + unit tests (RED→GREEN) → ยืนยัน 3 endpoint คืน JSON ถูก
2. **Demo data หลายปี** — ขยาย SQL + validate query จริง (เหมือนที่ทำมา)
3. **Frontend** — api+queries+page+3 components+router+menu
4. **Build + test** — `npm run test:unit`; build deploy SPA (`VITE_BASE=/hr-budget/public/app/`) → commit `public/app/`
5. **Deploy** — push main → Plesk **pull** (ได้ทั้ง backend ใหม่ + SPA ใหม่) → re-import demo SQL หลายปี

> **ผลต่อ deploy:** ฟีเจอร์นี้แตะ frontend → ต้อง **rebuild SPA + redeploy (commit→pull)** ไม่ใช่แค่ import SQL เหมือน demo data ที่ผ่านมา

---

## 7. คำถามที่ปิดแล้ว
- Forecast = ยอดจัดสรร (allocated) ÷ 12 เฉลี่ยรายเดือน ✅
- ไม่แก้ schema (ใช้ field เดิม `budget_requests.total_amount` + `request_status='approved'` เป็น "อนุมัติตาม พรบ") ✅

## 8. ความเสี่ยง/ข้อสังเกต
- "อนุมัติตาม พรบ" ใช้ `total_amount` ของคำขอที่ approved — ถ้าจริง พรบ อนุมัติ "ยอดที่ปรับลด" (ไม่เท่ายอดขอ) จะต้องเพิ่ม field `approved_amount` ภายหลัง (เฟส 2)
- comparison รายปีต้องมี demo หลายปี ถึงจะมีความหมาย (เฟส 2 จัดการ)

## 9. แก้ไขจาก expert review (v2 — 5 ด้าน: architecture/database/php/security/frontend)

**[BLOCKER ต้องแก้ design ก่อน implement]**

1. **Feature A ใช้แหล่งเดียวตลอด (CRITICAL):** เดิมดึง ปี/ไตรมาส/เดือน จาก 3 ตารางคนละชุด (budget_trackings.fiscal_year / chain record_month / budget_transactions.created_at) → ตัวเลขขัดกันเองในหน้าเดียว. **แก้: ดึงทั้ง 3 มิติจาก `budget_trackings` ตรงๆ (มี `fiscal_year` + `record_month` + index `idx_trackings_month`) เลิกใช้ `budget_transactions` (prod มี 2 แถว, JOIN กลับ trackings ไม่ได้).** → ปี = Σไตรมาส = Σเดือน และตรงกับ DashboardService.
2. **RBAC org-scope (CRITICAL security):** endpoint รวมเงินจากตารางที่ Phase 9/10 scope ไว้แล้ว (budget_trackings/budget_requests) แต่ design ไม่ scope → user หน่วยงานเดียวเห็นยอดทั้งกระทรวง = data-exposure regression. **แก้: inject `AccessScopeResolver` + ใช้ `orgScopeFilter()`/`readableOrgIds()` แบบ `DisbursementService` (null=ไม่กรอง, []=deny-all 1=0, [ids]=IN) ใส่ใน WHERE ทุก query ก่อน GROUP BY.** (decision: ดูข้อ §10)
3. **Input validation (HIGH):** สร้าง **`AnalyticsQueryDto`** (input DTO `fromQueryString()`+`validate()` แบบ `BudgetRequestListQueryDto`): whitelist `dimension ∈ {year,quarter,month}` (กัน SQL injection ผ่าน GROUP BY/identifier), `fiscal_year` เป็น int ช่วง 2500–2600 ไม่งั้น 422.
4. **ไม่สร้าง response DTO (HIGH):** โค้ดเบสนี้ Service คืน `array` + `@return array{...}` phpdoc (แบบ DashboardService) ไม่มี response-DTO ที่ไหนเลย → ใช้ array shape, มีแต่ **input** DTO (`AnalyticsQueryDto`).
5. **Frontend ใช้ `apiFetch()` ไม่ใช่ `apiUrl()` ดิบ (HIGH):** `api/analytics.ts` ต้องใช้ `apiFetch()` (แนบ cookie+CSRF+401) แบบ `api/dashboard.ts` — `apiUrl()` ดิบใช้แค่ href ดาวน์โหลด. query key ต้องรวม `dimension` (เปลี่ยน toggle = เปลี่ยน shape). empty-state แยกต่อ section — **ห้ามใช้ `some(n>0)`** กับ comparison (จะซ่อนปี 2570 ที่เบิกน้อย ซึ่งเป็นจุดสำคัญ).

**[ปรับระหว่าง implement]**

6. **COALESCE ทุก SUM ที่ nullable:** `SUM(COALESCE(allocated,0)+COALESCE(transfer,0))` (allocated เป็น NULL ได้) — ตาม BudgetExecutionRepository.
7. **Feature C JOIN org:** grand total ไม่ JOIN (นับ org_id NULL ด้วย); breakdown ใช้ **LEFT JOIN** + `COALESCE(o.name_th,'ไม่ระบุ')` ไม่งั้นยอดรวม breakdown ≠ KPI.
8. **แตก repo ตามโดเมน:** A/B (chain เบิกจ่าย) กับ C (`budget_requests`) ไม่แชร์ logic → แยก repo/กลุ่ม method, คุม <800 บรรทัด; สกัด chain JOIN + quarter-CASE ที่ซ้ำกับ `BudgetExecutionRepository` เป็น const/trait ร่วม.
9. **business rule ใน Service ชั้นเดียว:** forecast = `SUM(allocated)/MONTHS_PER_YEAR(=12 const)`, cumulative = running-sum (Service หรือ client `computed`) ไม่ฝังใน SQL; เผื่อสลับเป็น `approved_amount` เฟส 2 แก้จุดเดียว.
10. **SQLite test fixtures ต้องมี column `record_month` บน budget_trackings** (fixtures เดิมไม่มี) ไม่งั้น unit test fail.
11. **index:** เพิ่ม `idx_requests_year_status (fiscal_year, request_status)` บน budget_requests (เฟส go-live; demo ไม่จำเป็น).
12. **Frontend UX:** year selector เดียวบน header (disable ในโหมดเปรียบเทียบรายปี + hint); ห่อ 3 section ด้วย PrimeVue `Tabs` (lazy query ต่อ tab, ลด first-paint); reuse `HorizontalBarChart`+`StatCard`, สร้างใหม่ `ComparisonChart`(grouped bar, legend)+`ForecastChart`(ต้อง `ChartJS.register(LineElement,PointElement,LineController)`); cumulative = client `computed`; ใช้ `Intl.NumberFormat('th-TH')` บาท/compact, ป้ายปี BE, อัตราอนุมัติใช้ `%` ไม่ใช่บาท; respect `prefers-reduced-motion`.
13. **Demo data:** ถ้า A ใช้ budget_trackings ตรงๆ → seed 2568/2570 ที่ budget_trackings (มี record_month กระจายเดือน) ก็พอ ไม่ต้องมี disbursement_sessions ครบทุกปี; ตรวจ record_month distribution.
14. **error handling:** ทุก method `try/catch(\Throwable)` → log + `ApiResponse::error('ข้อความไทยทั่วไป',500)` ห้าม leak `$e->getMessage()`.

## 10. Decision (ปิดแล้ว)
**RBAC org-scope ของ analytics = ✅ SCOPE ตามสิทธิ์** (user เลือก 2026-06-19) — `AnalyticsService` inject `AccessScopeResolver`, รับ `$user`, ใช้ `orgScopeFilter()`/`readableOrgIds()` แบบ `DisbursementService` กรองทุก query ก่อน GROUP BY (null=ไม่กรอง สำหรับ admin/hasAll, []=deny 1=0, [ids]=IN). Feature C กรองทั้ง `budget_requests.org_id` และ org JOIN. Controller ต้องส่ง `Auth::user()`/JWT user payload เข้า service. เพิ่ม `scope` metadata ใน response (all/subtree) ให้ frontend แสดง disclaimer ได้.

## 11. สถานะ: design v2 finalized ✅ (ผ่าน expert review 5 ด้าน, decision ปิดครบ) — พร้อม implement เฟส 1
