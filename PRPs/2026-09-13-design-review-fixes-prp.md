# PRP: แผนแก้ Design Review M1–M12 (6.0 → ≥8.0)

## Metadata

- วันที่: 2026-09-13 (v2r6 — PRP-format + แก้ตามรีวิวรอบ 6 ซึ่งให้ READY 8/10)
- สถานะ: รอ approve
- Complexity: medium (7 PRs, frontend-only, 1 ไฟล์ใหม่, 2 specs ใหม่)
- Source PRD: `PRPs/2026-09-13-design-review-fixes-prd.md`
- PRD Phase: R1–R6 ทั้งหมด
- Review: `.claude/PRPs/plan_reviews/2026-09-13-design-review-fixes-prp-review.md`,
  รอบ 2: `.claude/PRPs/plan_reviews/2026-09-13-design-review-fixes-prp-review-r2.md`,
  รอบ 3: `.claude/PRPs/plan_reviews/2026-09-13-design-review-fixes-prp-review-r3.md`,
  รอบ 4: `.claude/PRPs/plan_reviews/2026-09-13-design-review-fixes-prp-review-r4.md`,
  รอบ 5: `.claude/PRPs/plan_reviews/2026-09-13-design-review-fixes-prp-review-r5.md`,
  รอบ 6: `.claude/PRPs/plan_reviews/2026-09-13-design-review-fixes-prp-review-r6.md`

## Summary

ปิด findings M1–M12 + minors จาก design review/live pass ให้คะแนนรวม ≥ 8.0
(Accessibility ≥ 7, Responsiveness ≥ 7) โดยทุก phase มีหลักฐานวัดได้จาก audit spec.
แผนฉบับนี้ปักเป้าหมายทุก task ด้วย file:line ที่ verify ด้วย grep แล้ว
(เก็บวิธี verify ไว้ใน Corrections ท้ายไฟล์)

## Success Criteria (Acceptance)

- `npm run test:e2e:audit` (mock): axe serious/critical = 0, contrast fails = 0,
  overflow360 = 0 ทุก route (baseline: axe 9 non-artifact, contrast 5 engine
  + info Message/logo — ยืนยันยอดรวมใน T0.1, overflow 26 หน้า)
- `composer verify` + `cd frontend && npm run verify` + `cd frontend && npm run test:unit`
  เขียวทุก PR
- `test-results/audit/summary.json` ไม่ถดถอยเทียบ baseline เมื่อจบทุก phase
- Manual: keyboard-only โฟลว์หลักผ่าน (B/D1), รีวิว shots 360px ทุก route (C),
  screen reader ใดก็ได้ 1 รอบ (E)

## Files to Change

ไฟล์ใหม่ 3 ไฟล์ (1 lib + 2 specs): `frontend/src/lib/focusTrap.ts`,
`frontend/src/lib/__tests__/focusTrap.spec.ts`, `frontend/src/lib/__tests__/tokens.spec.ts`
(ที่เหลือคือแก้ไฟล์เดิม; backend ไม่แตะ; ไม่มี dependencies ใหม่ —
imports ทั้งหมด resolve จากของเดิมใน repo)

| Phase | ไฟล์ | บรรทัด/จุด |
|---|---|---|
| A | `frontend/tailwind.config.js` | 16–20 (600→#0369a1, เพิ่ม 700 #075985) |
| A | `frontend/src/main.ts` | 15–31 (preset: เพิ่ม components.message) |
| A | `frontend/src/pages/DisbursementWizardPage.vue` | 302 (pill), 304, 384, 398, 434 (900/300), 365, 417, 491, 563 (hover) |
| A | `frontend/src/components/ApprovalChainPanel.vue` | 58 (200, ใน string ที่ script return), 135 (300) |
| A | `frontend/src/pages/LoginPage.vue` | 91 (logo), 155 (300) |
| A | `frontend/src/pages/NotificationListPage.vue` | 63 (hover verify), 114 (300) |
| A | `frontend/src/pages/DisbursementListPage.vue` | 140 (hover verify) |
| A | `frontend/src/pages/RequestListPage.vue` | 129, 140, 170 (hover verify) |
| A | `frontend/src/pages/RequestCreatePage.vue` | 156 (hover) |
| A | `frontend/src/pages/RequestDetailPage.vue` | 153 (hover) |
| A | `frontend/src/pages/RequestEditPage.vue` | 142 (hover) |
| B | `frontend/src/components/ItemEditor.vue` | 64–70, 73–80, 83–90, 96–102 (aria-label), 105–113 (ปุ่มลบ) |
| B | `frontend/src/pages/PersonnelAllowancePage.vue` | 134, 138, 143, 155, 168, 172 |
| B | `frontend/src/pages/PersonnelAssignmentPage.vue` | 130, 134, 139, 151, 163 |
| B | `frontend/src/pages/PersonnelBudgetPolicyPage.vue` | 143, 154, 158, 163, 167 |
| B | `frontend/src/pages/VacancyRecruitmentPage.vue` | 133, 146, 157, 168, 172 |
| B | `frontend/src/pages/AllowanceTypeListPage.vue` | 242, 257 + 309–323; checkboxes 226/233/236/261 verify-only (ห่อแล้ว) |
| B | `frontend/src/pages/PositionListPage.vue` | 356, 365, 374 + dialog 441–542 (verify-first) |
| B | `frontend/src/pages/SalaryRaisePage.vue` | 121–124 (ToggleSwitch) |
| B | `frontend/src/layouts/AppLayout.vue` | 68 (root), 73–84 (drawer), 286 (main) |
| B | `frontend/src/pages/BudgetExecutionPage.vue` | 109–113 (export anchor) |
| B | `frontend/src/pages/DocumentVaultPage.vue` | 239–253 (breadcrumb) |
| B | icons `aria-hidden` | AppLayout, NotificationBell, RequestApprovalChart, AnalyticsPage, BudgetExecutionPage, DashboardPage, LoginPage (เติม :92/:186; ข้าม :102/:122/:142/:143), NotificationListPage |
| B | `frontend/src/style.css` | @layer base (color-scheme) |
| C | `frontend/src/style.css` | เพิ่ม `.table-scroll` ใน @layer components |
| C | 24 ไฟล์ DataTable (29 จุด) | ดูรายการเต็มใน Task C1 (รวม CategoryItemsPanel.vue:133) |
| C | `frontend/src/pages/RequestDetailPage.vue` | 200 (native table) |
| C | `frontend/src/pages/BudgetExecutionPage.vue` | 170 (ลบ prop) |
| C | `frontend/src/components/PageHeader.vue` | 6 (flex-wrap) |
| C | `frontend/src/layouts/AppLayout.vue` | 68, 241, 286 (clip + min-w-0) |
| C | `frontend/src/pages/NotificationListPage.vue` | 119 (break-words) |
| D1 | `frontend/src/components/QueryErrorState.vue` | ขยาย (retry prop + ปุ่ม) |
| D1 | red-500/10 4 จุด (แทน) | DisbursementWizardPage:313, RequestCreatePage:100, RequestDetailPage:101, RequestEditPage:92 (ดู T-D1.1) |
| D1 | rose 7 จุด | AnalyticsPage:151, 187, 224, BudgetExecutionPage:119, DashboardPage:48, NotificationListPage:82 (+DashboardPage:75 verify-first) |
| D1 | `frontend/src/pages/RequestDetailPage.vue` | 115 (live), 137 (live), 174 (reject form คงเดิม) |
| D1 | `frontend/src/pages/RequestEditPage.vue` | 1–10 (import toast), 35–38 (guard toast), ~146–152 (cancel guard) |
| D1 | `frontend/src/pages/DisbursementWizardPage.vue` | 265–268 (cancel guard) |
| D1 | `frontend/src/stores/disbursementWizard.ts` | 19–23 (เพิ่ม isDirty) |
| D1 | `frontend/src/pages/PersonnelAllowancePage.vue` | 50–52 (guard feedback) |
| D1 | `frontend/src/pages/PersonnelAssignmentPage.vue` | 48–50 (guard feedback) |
| D1 | `frontend/src/pages/SalaryScaleListPage.vue` | 46 (setFieldError), 68–72 (field error) |
| D1 | disabled hints | RoleListPage (~266), AllowanceTypeListPage:333, ComputeBudgetPage:121+128, DisbursementWizardPage:364+416+562, DocumentVaultPage:224 |
| D2 | `frontend/src/pages/RequestCreatePage.vue` | ~95–160 (ห่อ form) |
| D2 | generic confirms 5 จุด | AllowanceTypeListPage:159, PersonnelAllowancePage:71, PersonnelAssignmentPage:68, PositionListPage:334, TargetListPage:176 |
| D2 | `frontend/src/pages/RoleListPage.vue` | ~266 (title), dialog ~284–340 (perm labels verify-first) |
| D2 | `frontend/src/pages/ComputeBudgetPage.vue` | 43–47 (lookup), 146 (column) |
| D2 | `frontend/src/pages/LoginPage.vue` | 94 (h1, D5) |
| D2 | `frontend/index.html` | 6 (favicon verify-only — ถูกอยู่แล้ว) |
| E | 5 chart components | ComparisonChart, ForecastChart, HorizontalBarChart, MonthlyExpenditureChart, RequestApprovalChart (wrapper role=img) |
| E | `frontend/src/components/ComparisonChart.vue` | เพิ่ม h2 + empty (MIRROR RequestApprovalChart:123–131) |
| E | `frontend/src/components/ForecastChart.vue` | เพิ่ม h2 + empty (เหมือนกัน) |
| E | `frontend/src/pages/DashboardPage.vue` | ~86 (SR table) |
| E | `frontend/src/pages/AnalyticsPage.vue` | 3 panels (SR tables) |
| F | `.github/workflows/ci.yml` | job `audit` ใหม่ (ต่อจาก frontend ~:157–190) |
| F | `tests/e2e/audit/spa-audit.spec.mjs` | อัปเกรด soft assertion เดิม (:84) + เพิ่ม expects ที่เหลือ |

## NOT Building

- การตัดสินใจ DTCG `tokens/*.json` (adopt-or-remove) — strategic call แยก
- Keyboard shortcuts / bulk actions, ระบบ contextual help, redesign ภาพรวม
- งาน backend (ไม่แก้โค้ด backend; อ่าน DTO ได้ถ้าต้องเช็ค required fields)
- การเปิด `.app-dark` ทั้งแอป (GOTCHA ใน Task A2 — blast radius ใหญ่เกิน scope)
- Unit tests ใหม่นอกเหนือ 2 specs ที่ระบุ (focusTrap, tokens)
- `jargon` copy sweep (PRD ไม่นิยามตัวอย่าง — scope ออกใน T-D2.3 แล้ว)

## Context — สำนวนและคำสั่งที่ต้อง reuse

- FormField: `import FormField from '@/components/FormField.vue'`
  (SOURCE `frontend/src/pages/CategoryListPage.vue:14`)
  props: `id` (บังคับ), `labelledBy?`, `label`, `error?`;
  control ใน slot ผูก `:aria-describedby="...`${id}-error`..."`
  (SOURCE `frontend/src/components/FormField.vue:1–29`)
- QueryErrorState: `import QueryErrorState from '@/components/QueryErrorState.vue'`
  (SOURCE `frontend/src/pages/CategoryListPage.vue:15`);
  ปัจจุบันมีแค่ Message error — D1 ขยายก่อนใช้
  (SOURCE `frontend/src/components/QueryErrorState.vue:1–14`)
- Toast: `import { useToast } from 'primevue/usetoast'` + `const toast = useToast()`
  (SOURCE `frontend/src/pages/SalaryScaleListPage.vue:6,26`);
  Toast/ConfirmDialog global อยู่แล้ว
  (SOURCE `frontend/src/layouts/AppLayout.vue:69–70`)
- Confirm: `import { useConfirm } from 'primevue/useconfirm'`
  (SOURCE `frontend/src/composables/useDeleteConfirm.ts:1–17`)
- ปุ่ม native: `rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white
  hover:bg-primary-500 disabled:opacity-50`
  (SOURCE `frontend/src/pages/RequestCreatePage.vue:156`)
- aria-label บน PrimeVue: `aria-label="เลือกปีงบประมาณ"`
  (SOURCE `frontend/src/pages/BudgetExecutionPage.vue:96`)
- title hint: `title="ลบรายการ"` (SOURCE `frontend/src/components/ItemEditor.vue:110`)
- Unit spec: `src/**/__tests__/*.spec.ts`, vitest + happy-dom, สไตล์ Arrange/Act/Assert
  (SOURCE `frontend/src/lib/__tests__/date.spec.ts:1–25`,
  `frontend/vitest.config.ts`)
- Contrast CLI: `python3 scripts/contrast.py "#fg" "#bg"` — exit 0 ถ้าผ่าน AA normal
  (SOURCE `scripts/contrast.py:1–10`)
- Audit: `npm run test:e2e:audit` (mock, ไม่ต้องมี backend) + SPA ที่
  `BASE_URL` (default `http://localhost:5174`); output
  `test-results/audit/summary.json`
  (SOURCE `tests/e2e/audit/spa-audit.spec.mjs:1–14`, `package.json:11`)

## Before / After (WCAG ที่อ้างอิง: 1.4.3, 1.4.11, 2.4.1, 2.5.8, 4.1.2, 1.1.1, 4.1.3)

- ปุ่มขาวบน primary-600: 4.10:1 → ≥4.5:1 (เกณฑ์ 1.4.3)
- hover ขาวบน primary-500 (2.77) → hover บน primary-700 ≥4.5:1
- Message info (3.24) → ข้อความ sky-400 บนพื้นเดิม ≥4.5:1
- วงกลม logo login (2.77) → พื้น primary-600 ใหม่ (เกณฑ์ non-text 3.0, 1.4.11)
- axe label 4 + target-size 1 → 0; controls Phase 9 ~30 จุดมีชื่อครบ (3.3.2/4.1.2)
- ไม่มี skip link → skip link → #main (2.4.1); drawer มี focus trap + คืนโฟกัส
- overflow360 26 หน้า → 0; ตาราง scroll ใน card; PageHeader wrap
- error states 13 จุดไม่สม่ำเสมอ → QueryErrorState + Retry 10 จุด
  (2 จุดเป็น content/form คงเดิม + 1 verify-first)
- dirty/cancel เงียบ → confirm; save ฟิลด์ขาดเงียบ → toast; mutation → live region (4.1.3)
- canvas 5 ตัวไร้ชื่อ → role=img + aria-label + ตาราง SR (1.1.1)

## Key Decisions

- D1 แก้ token แทนรายจุด (M1/M12/M7): คงเดิม
- D2 `FormField` ทั้ง Phase 9 (M10): คงเดิม
- D3 scroll wrapper ตัวเดียว (M4): เลือก **class `.table-scroll` ใน `style.css`**
  (MIRROR `.nav-link`/`.badge`, SOURCE `frontend/src/style.css:34–73`)
  — ปฏิเสธ component (ต้องแตะ import 24 ไฟล์) และ PrimeVue `scrollable` (ผูก API รุ่น)
- D4 แยก PR ตาม phase (A–E + F): คงเดิม — ห้ามยุบรวมตอน publish โดยไม่แจ้งก่อน
- D5 ชื่อ product: `<title>` ชนะ — `ระบบจัดการงบประมาณทรัพยากรบุคคล (HR Budget)`
  (SOURCE `frontend/index.html:7`) แทน login h1 `ระบบบริหารงบประมาณบุคลากร`
  (SOURCE `frontend/src/pages/LoginPage.vue:94`)
- D6 hover states: เพิ่ม token `primary-700 #075985` (sky-800 — v2 เขียน sky-700 ผิด)
  แล้วชี้ hover พื้นทึบทั้งหมดมาที่ 700 — แทนการคง primary-500 (2.77:1 ตก);
  หมายเหตุ: primary-600 ใหม่ #0369a1 ตรงกับ sky-700 พอดี (ค่าถูก ชื่อสเกลต่างกันเท่านั้น)
- D7 QueryErrorState: ขยาย component ก่อน (เพิ่ม prop `retry` + ปุ่ม "ลองอีกครั้ง")
  แล้วค่อยแทน 10 จุด (อีก 2 เป็น content/form คงเดิม + 1 verify-first) —
  ไม่สร้าง error component คู่ขนาน

## Step-by-Step Tasks

### Phase 0 — Baseline (ไม่ต้องมี PR)

- T0.1 ACTION: รัน audit mock เก็บ baseline
  - IMPLEMENT: Terminal 1 `cd frontend && npm run dev` (รอจน port 5174 พร้อม);
    Terminal 2 (repo root) `npm run test:e2e:audit`; สำเนา
    `test-results/audit/summary.json` + shots เก็บไว้อ้างอิงทุก PR ถัดไป
  - MIRROR: ขั้นตอนเดียวกับหัวไฟล์ audit
    (SOURCE `tests/e2e/audit/spa-audit.spec.mjs:5–8`)
  - VALIDATE: `summary.json` มีครบ 31 routes + ตัวเลขตรง baseline ที่บันทึกใน PRD
  - GOTCHA: spec มี soft axe assertion อยู่แล้ว (:84) — baseline อาจแดงได้
    บันทึกตัวเลขเสมอไม่ว่าผลรันจะเป็นอย่างไร (v2r5 เขียนว่า "ไม่มี expect" ผิด)

### Phase A — Tokens + contrast (PR-1) [M1, M12, M7, logo]

- T-A1 ACTION: เปลี่ยนค่า token + เพิ่ม 700
  - IMPLEMENT: `frontend/tailwind.config.js:16–20` — `600: '#0284c7' → '#0369a1'`,
    เพิ่ม `700: '#075985'`; `:24` เปลี่ยน `require('tailwindcss-primeui')` เป็น ESM
    `import primeui from 'tailwindcss-primeui'` + `plugins: [primeui]`
    (เพื่อให้ tokens.spec import config ได้); ชี้ hover พื้นทึบ
    `hover:bg-primary-500 → hover:bg-primary-700` ที่ DisbursementWizardPage:365,
    417, 491, 563, RequestCreatePage:156, RequestDetailPage:153, RequestEditPage:142;
    เขียน `frontend/src/lib/__tests__/tokens.spec.ts` ใน PR นี้ (ดู Testing Strategy —
    import config ต้องมี `// @ts-expect-error TS7016` บรรทัดก่อน เพราะ strict + ไม่มี allowJs)
  - MIRROR: hover class เดิมทั้งก้อน (SOURCE `RequestCreatePage.vue:156`)
  - VALIDATE: `python3 scripts/contrast.py "#ffffff" "#0369a1"` และ
    `"#ffffff" "#075985"` — exit 0 ทั้งคู่;
    `cd frontend && npm run test:unit` + `npm run verify` เขียว
  - GOTCHA: `#0369a1` วัดแล้ว 5.93:1 (PRD R1.1); ถ้า 700 ตกให้ลอง sky-900
    `#0c4a6e` (v2 เขียน sky-800 ผิด) แล้วบันทึกใน Deviations; ก่อนเริ่ม PR-1
    ให้รัน deploy build บน main baseline ให้เขียวครั้งหนึ่งก่อน (isolate ปัญหา
    จาก ESM change :24)
- T-A2 ACTION: override สีข้อความ PrimeVue info Message
  - IMPLEMENT: `frontend/src/main.ts:15–31` — เพิ่ม key `components` ใน
    `definePreset(Aura, {...})`:
    `components: { message: { colorScheme: { light: { info: { color: '{sky.400}' } },
    dark: { info: { color: '{sky.400}' } } } } }`
  - MIRROR: โครง colorScheme.light/dark.info.color ของ Aura
    (SOURCE `frontend/node_modules/@primeuix/themes/dist/aura/message/index.mjs`,
    token `message.info.color`); PRD R1.3 เรียก `primary-400` = hex เดียวกับ
    `{sky.400}` `#38bdf8` (ต่างแค่ระบบชื่อ token)
  - VALIDATE: `cd frontend && npm run verify` (typecheck จับ preset shape ผิด);
    audit contrast fails หมวด message = 0 และไม่มี fails จาก PrimeVue
    primary-severity controls (HrBudgetPreset คง sky.* — ถ้ามี fails ให้
    disposition ใน Deviations)
  - GOTCHA: `.app-dark` แปะที่ `<html>` แล้ว (SOURCE `frontend/index.html:2`) —
    PrimeVue จึงเรนเดอร์ dark scheme; override ทั้ง 2 schemes ไว้เผื่อกรณี
    selector หลุด (v2 เขียนกลับหัวว่า light active — ผิด)
- T-A3 ACTION: remap เฉดที่ไม่มีในธีม (4 ไฟล์)
  - IMPLEMENT: `text-primary-300 → text-primary-400` ที่
    DisbursementWizardPage:304, 398, 434, LoginPage:155, NotificationListPage:114,
    ApprovalChainPanel:135; `text-primary-200 → text-primary-400` ที่
    ApprovalChainPanel:58 (อยู่ใน string ที่ script return — remap เหมือนกัน);
    `bg-primary-900/40 → bg-primary-500/20` ที่ DisbursementWizardPage:304, 398;
    `bg-primary-900/20 → bg-primary-500/20` ที่ DisbursementWizardPage:384;
    LoginPage:155 เติม `hover:underline` (hover :300→:400 ชนสี base พอดี —
    underline ชดเชย cue ที่หาย; focus มี ring อยู่แล้ว)
  - MIRROR: `bg-primary-500/20` ที่มีอยู่แล้ว (SOURCE `NotificationListPage.vue:114`)
  - VALIDATE: `grep -rnE "primary-(200|300|900)" frontend/src` ได้ 0 ผล;
    `python3 scripts/contrast.py "#38bdf8" "#1e293b"` exit 0
  - GOTCHA: เฉดพวกนี้ปัจจุบัน generate เป็นค่าว่าง (inherit) — สีที่เห็นจะเปลี่ยน
    เล็กน้อย เทียบ screenshots ประกอบ
- T-A4 ACTION: วงกลม logo login + verify hover ข้อความ
  - IMPLEMENT: LoginPage:91 `bg-primary-500 → bg-primary-600`
  - MIRROR: avatar AppLayout (SOURCE `frontend/src/layouts/AppLayout.vue:277`)
  - VALIDATE: `python3 scripts/contrast.py "#ffffff" "#0369a1"` exit 0;
    audit contrast fails รวม = 0; `hover:text-primary-500` ที่
    DisbursementListPage:140, RequestListPage:129/140/170, NotificationListPage:63
    ต้องไม่โผล่ใน contrast fails — ถ้าโผล่ให้ชี้เป็น `primary-400` ใน PR นี้
  - GOTCHA: อย่าแตะ `text-primary-500` ของไอคอนตกแต่ง (AppLayout:87) — non-text
    มีข้อความกำกับอยู่แล้ว

### Phase B — A11y markup P0 (PR-2) [M2, M8, M9, M10, target-size]

- T-B1 ACTION: ตั้งชื่อ controls ใน ItemEditor (WCAG 4.1.2)
  - IMPLEMENT: `frontend/src/components/ItemEditor.vue` — เติม
    `:aria-label="`แถว ${index + 1} ชื่อรายการ`"` (input :64–70),
    `จำนวน` (:73–80), `ราคาหน่วย` (:83–90), `หมายเหตุ` (:96–102);
    ปุ่มลบ :105–113 เป็น `:aria-label="`ลบ ${item.item_name || `แถว ${index + 1}`}`"`
    (+ title เดียวกัน)
  - MIRROR: title idiom (SOURCE `ItemEditor.vue:110`)
  - VALIDATE: axe `label` บน /requests/create + /requests/1/edit = 0;
    `cd frontend && npm run verify` เขียว
  - GOTCHA: placeholder ไม่ใช่ชื่อที่เข้าถึงได้ — ห้ามใช้แทน aria-label
- T-B2 ACTION: ย้าย controls Phase 9 เข้า FormField (WCAG 3.3.2)
  - IMPLEMENT: ห่อ controls ต่อไปนี้ด้วย `<FormField id label>` + `:error` ถ้ามี —
    PersonnelAllowancePage:134, 138, 143, 155, 168, 172;
    PersonnelAssignmentPage:130, 134, 139, 151, 163;
    PersonnelBudgetPolicyPage:143, 154, 158, 163, 167;
    VacancyRecruitmentPage:133, 146, 157, 168, 172;
    AllowanceTypeListPage:242, 257 (Select/Input ใน dialog edit),
    309–323 (ฟอร์ม rates 7 controls);
    Checkbox :226/:233/:236/:261 verify-only (ห่อ `<label>` อยู่แล้วที่ :225–228,
    :232–237, :260–262 — ยืนยันแล้วไม่ต้องแตะ ห้าม double-wrap; :226 ไม่ต้องเติม
    aria-label เพราะมี label แล้ว)
  - MIRROR: FormField + import (SOURCE `FormField.vue:1–29`, `CategoryListPage.vue:14`);
    Select ใช้ prop `labelled-by` ใน template
    (SOURCE `SalaryScaleListPage.vue:155`, prop `labelledBy` ใน FormField)
  - VALIDATE: axe `label` บน 5 หน้า Phase 9 ของ T-B2 (+ /positions ของ T-B3) = 0;
    `npm run verify` เขียว
  - GOTCHA: PrimeVue Select/InputNumber เรนเดอร์ input ซ้อน — ใช้ `labelled-by`
    + `input-id` (มี precedent ที่ PositionListPage:494, 518) อย่าใช้ `for` ตรง
- T-B3 ACTION: ฟิลเตอร์ Position + ToggleSwitch รอบเงินเดือน
  - IMPLEMENT: PositionListPage:356, 365 (Select) + :374 (search) เติม label/
    aria-label; dialog :441–542 verify-first (controls มี id `pos-*` แล้ว —
    ถ้าไร้ label ให้ห่อ FormField); SalaryRaisePage:121–124 เติม
    `:aria-label="`นับรอบ ${formatThaiDate(data.effective_date)} ในงบ`"`
    (`formatThaiDate` + `effective_date` มีใช้อยู่แล้วที่ :114)
  - MIRROR: aria-label บน Select (SOURCE `BudgetExecutionPage.vue:96`)
  - VALIDATE: axe `label` บน /positions + /salary-raise-rounds = 0
  - GOTCHA: ToggleSwitch ในตารางต้องระบุตัวตนแถว — ห้าม label กลางๆ ว่า "นับในงบ" เฉยๆ
- T-B4 ACTION: skip link + drawer focus (WCAG 2.4.1/2.4.11)
  - IMPLEMENT: AppLayout — เติม skip link ต้น template:
    `<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4
    focus:top-4 focus:z-50 focus:rounded focus:bg-primary-600 focus:px-4
    focus:py-2 focus:text-white">ข้ามไปเนื้อหา</a>`;
    `main` :286 เติม `id="main"`; drawer (`aside#app-sidebar` :80–84):
    โฟกัสเข้าตอนเปิด + trap Tab + คืนโฟกัสปุ่ม Menu (:247) ตอนปิด +
    Escape ปิด (reuse `useEscapeClose`, SOURCE `composables/useEscapeClose.ts`);
    เขียน `frontend/src/lib/focusTrap.ts` + spec ใน PR นี้ (ดู Testing Strategy)
  - MIRROR: focus-trap ใหม่ `frontend/src/lib/focusTrap.ts` (เขียนใหม่ + spec —
    ดู Testing Strategy); sr-only เป็น utility ของ Tailwind มีอยู่แล้ว
  - VALIDATE: keyboard walkthrough (Tab จาก address bar → skip link โผล่ → Enter
    ข้ามได้; เปิด drawer ด้วย keyboard → Tab วนใน drawer → Escape ปิด + โฟกัสกลับ);
    `npm run test:unit` (focusTrap spec) เขียว
  - GOTCHA: overlay :73–77 เป็น div เปล่า — เติม `aria-hidden="true"` ด้วย
- T-B5 ACTION: Export anchor เดียว + breadcrumb (WCAG 2.5.8/4.1.2)
  - IMPLEMENT: BudgetExecutionPage:109–113 — แทน `a > Button` ด้วย `<a>`
    เดียว carrying classes `p-button p-component p-button-success` + ไอคอน
    Download (import เดิม) + ข้อความ "ส่งออก Excel"; คง pattern disabled
    (`pointer-events-none opacity-50` + เติม `aria-disabled`) เมื่อ `!hasData`;
    DocumentVaultPage:239–253 — crumb สุดท้ายเรนเดอร์เป็น `<span
    aria-current="page">` แทน button; buttons ที่เหลือเติม `min-h-6`
  - MIRROR: disabled pattern เดิม (SOURCE `BudgetExecutionPage.vue:109`)
  - VALIDATE: axe `nested-interactive` + `target-size` = 0; เทียบ screenshot ปุ่ม
    Export ก่อน/หลัง (สีเขียว success ต้องเหมือนเดิม)
  - GOTCHA: Button มี prop `as` จริง (SOURCE `primevue/button/index.d.ts:136` —
    v2 อ้างว่าไม่มี ผิด) แต่ PR นี้ใช้ anchor แต่ง class ตรงอยู่ดี (link semantics
    ของไฟล์ดาวน์โหลด + คุม disabled ด้วย pattern เดิม) — VALIDATE ภาพจับความต่าง
- T-B6 ACTION: icons + base CSS + p-in-button (verify-first)
  - IMPLEMENT: เติม `aria-hidden="true"` ทุก lucide icon ตกแต่ง (43 จุด ใน 8 ไฟล์ —
    AppLayout 29 รวม :264 `<Calendar class="mr-1 h-4 w-4" />` (class-not-first),
    NotificationBell:91, RequestApprovalChart:129,
    AnalyticsPage:125/166/202, BudgetExecutionPage:141, DashboardPage:83,
    LoginPage:92/186 (เติมใหม่; ข้าม :102/:122 (multi-line นอกเซ็ต grep) และ
    :142/:143 (ในเซ็ต) ที่มี aria-hidden อยู่แล้ว),
    NotificationListPage:65/92); locate ทุกจุดด้วย
    `grep -rn -oE '<[A-Z][A-Za-z0-9]*[^>]*class="[^"]*h-[0-9]' frontend/src --include="*.vue"`
    (คาด 43 จุด ล้วน lucide — รวม class-not-first อย่าง :264);
    `style.css` @layer base เติม `color-scheme: dark` ที่ `:root`;
    p-in-button: แก้ `<p>` ใน `<button>` เป็น `<span>` (คง classes) ที่
    NotificationBell.vue:141–143 (ใน button :131–146) และ
    NotificationListPage.vue:111/:119/:120 (ใน button :99–122);
    ปิดท้ายด้วย locator `button p` ใน audit — ต้องได้ 0
  - MIRROR: aria-hidden ที่มีอยู่ (SOURCE `LoginPage.vue` — ไฟล์เดียวที่มี)
  - VALIDATE: widened grep ข้างบน 43 จุดมี aria-hidden ครบ (41 เติมใหม่ +
    2 เดิมในเซ็ต :142/:143; อีก 2 เดิม :102/:122 อยู่นอกเซ็ตเพราะ multi-line);
    locator `button p` = 0; axe `label` รวม = 0
  - GOTCHA: ไอคอนในปุ่ม Export (T-B5) อยู่ใต้ anchor ข้อความ — นับว่าตกแต่ง ให้
    aria-hidden เช่นกัน

### Phase C — Responsive (PR-3) [M4 + PageHeader]

- T-C1 ACTION: สร้าง `.table-scroll` + ครอบตารางทั้งหมด (29 DataTables + 1 native)
  - IMPLEMENT: `frontend/src/style.css` @layer components เพิ่ม
    `.table-scroll { @apply overflow-x-auto; }` (+ `-webkit-overflow-scrolling:
    touch`); ห่อ `<DataTable>` ทุกจุดด้วย `<div class="table-scroll">` —
    AllowanceTypeListPage:180, 273; BudgetExecutionPage:165, 205;
    CategoryListPage:117; ComputeBudgetPage:143; DisbursementListPage:123;
    DivisionListPage:126; DocumentVaultPage:312; FiscalYearListPage:156;
    OrganizationListPage:129; PersonnelAllowancePage:96; PersonnelAssignmentPage:93;
    PersonnelBudgetPolicyPage:105; PlanListPage:138; PositionListPage:379, 554,
    682; RequestListPage:112; RoleListPage:206; SalaryRaisePage:99, 153;
    SalaryScaleListPage:118; TargetListPage:198; TargetTypeListPage:115;
    UserAccessGrantsPage:139; UserListPage:140; VacancyRecruitmentPage:91;
    CategoryItemsPanel.vue:133 (component, เรนเดอร์บน /categories)
    (24 ไฟล์ 29 จุด — verify ด้วย grep `<DataTable` แล้ว);
    + native table RequestDetailPage:200; ลบ `responsive-layout="scroll"`
    ที่ BudgetExecutionPage:170 (dead prop ยืนยันแล้ว — grep `responsiveLayout`
    ใน primevue/datatable/ ได้ 0 ผล, ตกค้างจาก v3)
  - MIRROR: component class ใน style.css (SOURCE `style.css:34–73`);
    ItemEditor มี `overflow-x-auto` อยู่แล้ว (SOURCE `ItemEditor.vue:49`) — ไม่แตะ
  - VALIDATE: `grep -rn "<DataTable" frontend/src --include="*.vue" | wc -l` = 29
    และทุกจุดอยู่ใน `.table-scroll`; overflow360 = 0 ทั้ง 31 routes;
    รีวิว 360px shots ทุก route ด้วยตา
  - GOTCHA: BudgetExecutionPage:205 เป็นตารางซ้อนใน expansion — ห่อชั้นในด้วย;
    จุดเสี่ยงสุดของแผนนี้ (เปลี่ยน layout ตารางทุกหน้า)
- T-C2 ACTION: shell clip + PageHeader wrap + notification break
  - IMPLEMENT: AppLayout root :68 เติม `overflow-x-clip`; content col :241 +
    `main` :286 เติม `min-w-0`; PageHeader.vue:6 `flex items-center
    justify-between → flex flex-wrap items-center justify-between gap-3`;
    NotificationListPage:119 เติม `break-words`
  - MIRROR: PageHeader slot pattern คงเดิม (SOURCE `PageHeader.vue:6–12`)
  - VALIDATE: overflow360 หน้า notifications = 0 (shell 816px @360 ต้องหาย);
    PageHeader ทุกหน้า wrap ที่ 360px (รีวิว shots)
  - GOTCHA: ใช้ `overflow-x-clip` (ไม่ใช่ hidden) เพื่อไม่สร้าง scroll container
    ที่ดัก sticky/focus; `min-w-0` ต้องใส่ทั้ง :241 และ :286 (flex ซ้อน 2 ชั้น)

### Phase D1 — Errors + guards (PR-4a) [M5, M6, M11, announces]

- T-D1.1 ACTION: ขยาย QueryErrorState + แทน error states 10 จุด (WCAG 4.1.3)
  - IMPLEMENT: `QueryErrorState.vue` — เพิ่ม prop `retry?: () => void` +
    ปุ่ม "ลองอีกครั้ง" (MIRROR ปุ่ม native); แทน red-500/10 div ที่
    DisbursementWizardPage:313, RequestCreatePage:100, RequestDetailPage:101,
    RequestEditPage:92 และ rose div ที่ AnalyticsPage:151, 187, 224,
    BudgetExecutionPage:119, DashboardPage:48, NotificationListPage:82
    ด้วย `<QueryErrorState :error="..." :retry="refetch-fn" />`
    (retry ผูก query refetch ของหน้านั้น; ถ้าไม่มี refetch ให้ส่ง
    `() => window.location.reload()`)
  - MIRROR: import + Message pattern (SOURCE `QueryErrorState.vue:1–14`,
    `CategoryListPage.vue:15`)
  - VALIDATE: `grep -rn "red-500/10\|rose-800" frontend/src/pages` เหลือเฉพาะ
    RequestDetailPage:137 (content) + :174 (form) — ที่เหลือ 0;
    audit ไม่ regression; keyboard: Tab ถึงปุ่ม Retry ได้ทุกหน้า
  - GOTCHA: RequestDetailPage:137 (rejected_reason) กับ :174 (reject form)
    ไม่ใช่ error states — ห้ามแทน (137 ใส่ live region ใน T-D1.2);
    DashboardPage:75 (rose ในโซน chart) — verify-first ว่าเป็น error display
    จริงค่อยแทน
- T-D1.2 ACTION: live regions หลัง mutation (WCAG 4.1.3)
  - IMPLEMENT: RequestDetailPage:115 ห่อ `<StatusBadge>` ด้วย
    `<span aria-live="polite">`; :137 rejected_reason div เติม `aria-live="polite"`
  - MIRROR: `role="alert"` บน error div เดิม (SOURCE `RequestCreatePage.vue:100`)
    — ใช้ polite (ไม่ขัดจังหวะ) เพราะเป็น status ไม่ใช่ error
  - VALIDATE: interaction probe (approve/submit แล้ว badge/reason เปลี่ยน +
    SR ประกาศ); audit spec ไม่ regression
  - GOTCHA: อย่าใช้ `role="alert"` (assertive) กับ status ปกติ — รบกวน SR
- T-D1.3 ACTION: dirty guard wizard + RequestEdit (M6)
  - IMPLEMENT: `disbursementWizard.ts:19–23` เพิ่ม
    `const isDirty = computed(() => step.value > 1 || !!session.value ||
    !!record.value || Object.keys(amounts.value).length > 0)`
    (+ `computed` ใน import :2 ซึ่งปัจจุบันมีแค่ `ref`; + export `isDirty`
    ใน return block :64–76);
    `cancel()` (DisbursementWizardPage:265–268) — ถ้า dirty ให้ `useConfirm`
    ถามก่อน `wizard.reset()` + push; RequestEditPage — snapshot ค่าโหลดครั้งแรก
    (requestTitle/fiscalYear/orgId/items ใน watch :30–44) + `isDirty` computed +
    ดักคลิก link ยกเลิก (~:146–152) ด้วย confirm เมื่อ dirty
  - MIRROR: `useConfirm` + ConfirmDialog global
    (SOURCE `useDeleteConfirm.ts:1,10–17`, `AppLayout.vue:70`)
  - VALIDATE: interaction probe M6 (กรอก → Cancel → มี confirm; ไม่กรอก → ไปเลย);
    keyboard: confirm โต้ตอบด้วย Tab/Enter/Escape ได้
  - GOTCHA: `reset()` ล้าง store — confirm ต้องเกิดก่อน reset เสมอ
- T-D1.4 ACTION: feedback ฟิลด์ขาด + field error + edit-guard toast (M11/R4.3/R4.4)
  - IMPLEMENT: PersonnelAllowancePage:50–52 — แทน silent `return` ด้วย toast
    error ระบุฟิลด์ที่ขาด (person/อัตรา/ประเภท/วันมีผล); PersonnelAssignmentPage:48–50
    เหมือนกัน (person/อัตรา/หน่วยงาน/วันมีผล); SalaryScaleListPage:46 เพิ่ม
    `setFieldError` ใน destructure + :68–72 เรียก
    `setFieldError('max_amount', 'อัตราขั้นสูงต้องไม่ต่ำกว่าขั้นต่ำ')` (คง toast
    เดิมได้); RequestEditPage:35–38 — toast warn
    ("คำขอนี้ไม่อยู่ในสถานะที่แก้ไขได้") ก่อน `router.replace`
    (+ เพิ่ม `useToast` import — ปัจจุบันไฟล์นี้ไม่มี)
  - MIRROR: toast idiom (SOURCE `SalaryScaleListPage.vue:6,26`);
    FormField `:error` แสดงเอง (SOURCE `SalaryScaleListPage.vue:155+`)
  - VALIDATE: interaction probe M11 (กดบันทึกทั้งที่ฟิลด์ขาด → มี toast ทุกครั้ง);
    max<min → error ขึ้นที่ฟิลด์ (ดูด้วยตา + axe ไม่มี label error ใหม่);
    เปิด /requests/:id/edit ของคำขอ non-draft → มี toast + กลับหน้า detail
  - GOTCHA: toast อยู่ได้ 3–5s — ข้อความต้องระบุฟิลด์เป็นภาษาไทยชัดเจน ไม่ใช่
    "กรุณาตรวจสอบ" ลอยๆ
- T-D1.5 ACTION: hint บนปุ่ม disabled ที่เหลือ (R4.3)
  - IMPLEMENT: เติม `title` อธิบายเหตุผลที่: RoleListPage toggle (~:266,
    "บทบาทระบบปิดไม่ได้"); AllowanceTypeListPage:333 ("เลือกวันเริ่มมีผลก่อน");
    ComputeBudgetPage:121 + :128 ("เลือกปีงบก่อน"); DisbursementWizardPage:364
    ("ทำขั้นที่ 1 ให้ครบก่อน"), :416 ("เลือกกิจกรรมก่อน"), :562
    ("บันทึกไม่สำเร็จ — ดูข้อผิดพลาดด้านบน", เคส recordFailed; เคส pending มี label
    "กำลังบันทึก..." อยู่แล้ว); DocumentVaultPage:224 ("เลือกโฟลเดอร์ก่อนอัปโหลดไฟล์");
    Export anchor (T-B5) เติม title "ยังไม่มีข้อมูลให้ส่งออก" เมื่อ !hasData
  - MIRROR: title idiom (SOURCE `ItemEditor.vue:110`)
  - VALIDATE: hover/focus ทุกปุ่มข้างบนเห็นเหตุผล; `:disabled="saving/busy/pending"`
    (transient — ApprovalChainPanel, dialog ยกเลิก ฯลฯ) ไม่ต้องมี hint (excluded
    อย่างมีเหตุผล: สถานะชั่วคราว ไม่ใช่เงื่อนไข)
  - GOTCHA: PrimeVue Tooltip ไม่ได้ register (main.ts ไม่มี) — ใช้ native `title`
    อย่าเพิ่ม Tooltip directive ใน PR นี้

### Phase D2 — Copy + labels polish (PR-4b) [minors]

- T-D2.1 ACTION: RequestCreate ห่อ form + confirm ลบระบุตัวตน (R4.4/R4.6)
  - IMPLEMENT: RequestCreatePage ~:95–160 — ห่อฟิลด์+ปุ่มด้วย
    `<form @submit.prevent="saveAndSubmit">`; ปุ่มส่งอนุมัติ (:153–159) เป็น
    `type="submit"`; ปุ่มบันทึกร่าง (:146–152) เป็น `type="button"`
    (คง `@click="saveDraft"` เดิม);
    แก้ generic confirms 5 จุดให้ระบุตัวตน (MIRROR
    `ยืนยันลบหมวด "${cat.name_th}"?...`, SOURCE `CategoryListPage.vue:95`):
    AllowanceTypeListPage:159 → ใส่ชื่ออัตรา; PersonnelAllowancePage:71 →
    ใส่ person_id/จำนวน; PersonnelAssignmentPage:68 → ใส่ person_id/หน่วยงาน;
    PositionListPage:334 → ใส่ชื่อเงินเพิ่ม; TargetListPage:176 → ใส่ชื่อเป้าหมาย
  - MIRROR: form + submit (นี่คือ form แรกของฟิลด์นี้ — ไม่มี precedent ใน repo;
    ใช้ native form + `@submit.prevent` มาตรฐาน Vue)
  - VALIDATE: Enter ในฟิลด์ RequestCreate ส่งฟอร์มได้ (keyboard-only);
    เปิด confirm ลบทั้ง 5 จุดเห็นชื่อ/เลขทุกอัน; `npm run verify` เขียว
  - GOTCHA: ItemEditor อยู่ในฟอร์มนี้ด้วย — ปุ่ม +เพิ่มรายการ/ลบแถวต้องคง
    `type="button"` (มีอยู่แล้ว :105, :120) ไม่ให้ submit ฟอร์ม
- T-D2.2 ACTION: Role labels + compute org + product/favicon (minors)
  - IMPLEMENT: RoleListPage dialog (~:284–340, verify-first) — ถ้า permission
    picker แสดง `code` ดิบ ให้แสดง `name_th` (มีใน type แล้ว,
    SOURCE `types/rbac.ts:23–28`) + code เป็นตัวรอง (MIRROR :222–:223);
    ComputeBudgetPage:43–47 — `import { useOrganizationList } from
    '@/queries/useOrganizations'` (SOURCE `BudgetExecutionPage.vue:12`) + map
    `organization_id → name_th` ใน rows + :146 แสดงชื่อแทน id;
    LoginPage:94 h1 → `ระบบจัดการงบประมาณทรัพยากรบุคคล` (D5);
    index.html:6 verify-only — Vite rebase `/favicon.svg` ให้อัตโนมัติอยู่แล้ว
    (ยืนยันจาก deploy build; ห้ามเปลี่ยนเป็น `%BASE_URL%` เพราะ Vite ไม่แทนที่
    placeholder นี้ — grep ใน vite dist = 0)
  - MIRROR: name_th + code suffix (SOURCE `RoleListPage.vue:222–223`)
  - VALIDATE: role picker เห็นไทยทุกสิทธิ์; ตาราง compute เห็นชื่อหน่วยงาน;
    h1 ตรงกับ `<title>`; deploy build (`VITE_BASE=/hr_budget/public/app/`)
    โหลด favicon ได้ (เช็ค network tab — 200 ไม่ใช่ 404)
  - GOTCHA: favicon ไม่ต้องแก้โค้ด — ถ้า network check 404 (ไม่คาดว่าจะเกิด)
    ให้หยุดและ disposition ใน Deviations แทนการเดา path เอง (ห้ามใช้ relative
    path — พังบน deep SPA routes); role `code` ในตาราง (:223)
    เป็น identifier — คงไว้ ไม่ลบ
- T-D2.3 ACTION: ปัด copy ที่เหลือ (scoped)
  - IMPLEMENT: รัน `grep -rn "—" frontend/src --include="*.vue"` (คาด 82 จุด
    ใน 24 ไฟล์: FormField, HorizontalBarChart, RequestApprovalChart, AppLayout,
    AllowanceTypeListPage, AnalyticsPage, BudgetExecutionPage, ComputeBudgetPage,
    DashboardPage, DisbursementWizardPage, LoginPage, NotificationListPage,
    PersonnelAllowancePage, PersonnelAssignmentPage, PersonnelBudgetPolicyPage,
    PositionListPage, RequestCreatePage, RequestDetailPage, RoleListPage,
    SalaryRaisePage, SalaryScaleListPage, UserAccessGrantsPage, UserListPage,
    VacancyRecruitmentPage) — แทนเฉพาะ em-dash ที่เป็นเครื่องหมายวรรคตอนใน
    สตริงผู้ใช้ที่มองเห็น (เช่น "โหลดรายงานไม่สำเร็จ — ..." ที่
    BudgetExecutionPage:121) ด้วย `–`/คำไทย; คง `'—'` placeholder ค่าว่าง
    (`?? '—'`, `v-else>—`, เช่น AllowanceTypeListPage:196, 288, 297) และใน
    คอมเมนต์ไว้; `'1-12'`: PositionListPage:71 + :208 (zod messages ดิบ)
    → `'ต้องเป็นตัวเลข 1–12'`; :515 (label "เดือนที่นับ (1-12)") คงไว้
    (ช่วงตัวเลขกำกับไทยชัดเจนแล้ว); `jargon`: PRD ไม่นิยามตัวอย่าง —
    scope ออกจาก PR นี้ (เหตุผล: ไม่มีรายการคำต้องห้าม; ถ้าเจอระหว่าง implement
    ให้บันทึกใน Deviations แทน)
  - MIRROR: ไม่มี (งาน editorial ครั้งเดียว)
  - VALIDATE: re-grep `—` — ทุกจุดที่เหลือต้องเป็น placeholder (`?? '—'`) หรือ
    คอมเมนต์เท่านั้น (นอกนั้นคือหลุด); รีวิว diff ข้อความทั้งหมด + screenshots
    หน้าที่แตะ; audit ไม่ regression
  - GOTCHA: อย่า "แก้" `'—'` placeholder — มันคือ null display ที่ตั้งใจ;
    PR-4b เริ่มได้หลัง PRD owner sign-off เรื่อง jargon (ดู Open Questions)

### Phase E — Charts (PR-5) [M3]

- T-E1 ACTION: role=img + aria-label ทั้ง 5 chart components (WCAG 1.1.1)
  - IMPLEMENT: ใน `frontend/src/components/{ComparisonChart,ForecastChart,
    HorizontalBarChart,MonthlyExpenditureChart,RequestApprovalChart}.vue` —
    ห่อ chart render ด้วย
    `<div role="img" :aria-label="สรุป...">` (สรุปจาก props เช่น ยอดขอ/อนุมัติ,
    ช่วงเดือน — ข้อความไทย 1 ประโยค); ComparisonChart + ForecastChart เพิ่ม
    `<h2>` + empty state ใน component (MIRROR RequestApprovalChart.vue:123–131 —
    2 ไฟล์นี้ไม่มี h2/empty เลย ส่วน RequestApprovalChart มีแล้ว)
  - MIRROR: section + h2 + Inbox empty (SOURCE `RequestApprovalChart.vue:123–131`)
  - VALIDATE: assert DOM (role=img + aria-label ครบทุก chart instance);
    axe ไม่เพิ่ม violation; screen reader ใดก็ได้ 1 รอบ
  - GOTCHA: ห้ามแตะ canvas ที่ vue-chartjs render ตรงๆ (internals ไลบรารี) —
    role=img ทำให้ children เป็น presentational อยู่แล้ว
- T-E2 ACTION: ตารางข้อมูล SR บน dashboard + analytics
  - IMPLEMENT: DashboardPage ~:86 (หลัง MonthlyExpenditureChart) เพิ่ม
    `<table class="sr-only">` สรุปเดือน/ยอด; AnalyticsPage 3 panels
    (comparison/forecast/request) เพิ่มตาราง SR ต่อ panel (request panel ใช้
    requestReport เดียวกับ chart); execution ไม่ต้อง (มีตาราง breakdown แล้ว —
    เติมแค่ aria-label ใน T-E1)
  - MIRROR: sr-only utility ของ Tailwind; ตาราง breakdown
    (SOURCE `BudgetExecutionPage.vue:165–201`)
  - VALIDATE: SR อ่านตารางได้ครบ; ตารางมองไม่เห็นด้วยตา (รีวิว shot);
    หน้า request tab มี h2 + empty อยู่แล้ว (verify-only —
    SOURCE `RequestApprovalChart.vue:124–131`)
  - GOTCHA: ตาราง sr-only ต้องไม่กระทบ layout (เช็ค shot ว่าไม่มีช่องว่างเกิน)

### Phase F — Axe CI gate (PR-6, optional)

- T-F1 ACTION: เพิ่ม job audit + assertions
  - IMPLEMENT: `tests/e2e/audit/spa-audit.spec.mjs` — คง/อัปเกรด `expect.soft`
    เดิมที่ :84 (axe serious/critical หัก mock artifacts ที่ PRD บันทึกไว้) +
    เพิ่ม expects: contrast fails = 0, overflow360 = 0 (ห้ามเพิ่ม axe assertion
    ซ้ำซ้อนกับ :84);
    `.github/workflows/ci.yml` — job `audit` ใหม่ `needs: [frontend]`:
    checkout@v4 → setup-node@v4 (node 22, MIRROR frontend job :167–172) →
    `npm ci --ignore-optional` (root, MIRROR e2e :242) →
    `actions/download-artifact@v4` (name `frontend-dist`, path `frontend/dist`) →
    `npx playwright install --with-deps chromium` (MIRROR e2e :290) →
    `npx vite preview --host 127.0.0.1 --port 5174` background + poll จนพร้อม
    (MIRROR e2e :333–342) → `npm run test:e2e:audit` ด้วย `BASE_URL:
    http://127.0.0.1:5174` (MIRROR e2e :348) → upload `test-results/audit` →
    kill preview (MIRROR e2e :366)
  - MIRROR: frontend job steps (SOURCE `.github/workflows/ci.yml:157–190`),
    e2e gating (SOURCE `ci.yml` e2e `if:` — ผูกเงื่อนไขเดียวกันถ้า minutes แพง)
  - VALIDATE: ดู CI run บน PR นี้ (เขียว); ตั้งใจ break 1 จุดบน branch ทดสอบ
    แล้วดู job แดง (gate ใช้งานได้จริง)
  - GOTCHA: workflow หลักอาจ disabled อยู่ (เช็ค repo settings — ci.yml มี
    triggers ปกติ) + เพิ่ม minutes — ตัดสินใจ minutes ตั้งแต่เนิ่น ๆ (ก่อนจบ
    PR-5) เพราะ gate นี้คุมทุก phase;
    mock mode ไม่ต้องมี backend/MySQL (เบากว่า e2e มาก)

## Validation Commands (รวมทุก phase — รันได้จริงทั้งหมด)

- `composer verify` (repo root: PHPStan + PHPUnit Unit — PHP ไม่ถูกแตะแต่ gate คง)
- `cd frontend && npm run test:unit` (vitest run — รวม 2 specs ใหม่)
- `cd frontend && npm run verify` (typecheck + build)
- `npm run test:e2e:audit` (mock; ต้องมี SPA ที่ :5174 ก่อน — ดู T0.1)
- `python3 scripts/contrast.py "#fg" "#bg"` (exit 0 = ผ่าน AA normal)
- Manual: keyboard-only โฟลว์หลัก (B/D1), รีวิว shots 360px (C),
  screen reader ใดก็ได้ 1 รอบ (E)

## Testing Strategy

Unit ใหม่ 2 specs (input/expected):
- `frontend/src/lib/__tests__/tokens.spec.ts` — import config ด้วย
  `import tailwindConfig from '../../../tailwind.config.js'`
  (บรรทัดก่อนต้องมี `// @ts-expect-error TS7016: untyped JS config` —
  strict + ไม่มี allowJs, ไม่ใช้ .d.ts shim เพื่อไม่เพิ่มไฟล์ใหม่),
  assert ด้วยสูตร luminance เดียวกับ `scripts/contrast.py`:
  `#ffffff` on `#0369a1` ≥ 4.5 (expect ≈5.93),
  `#ffffff` on `#075985` ≥ 4.5,
  `#38bdf8` on `#1e293b` ≥ 4.5
  (MIRROR สไตล์ Arrange/Act/Assert, SOURCE `lib/__tests__/date.spec.ts:1–25`)
- `frontend/src/lib/__tests__/focusTrap.spec.ts` — `trapTabKey(container, event)`:
  Tab ที่ last → focus first; Shift+Tab ที่ first → focus last;
  Tab กลาง list → ปล่อยผ่าน (ไม่ preventDefault);
  container มี focusable 0 ตัว → ไม่ throw
- Edge-case checklist (manual + audit): 360px ครบ 31 routes; drawer trap +
  return focus; disabled-with-hint ทุกจุด T-D1.5; edit-guard toast (draft vs
  non-draft); empty states ว่างจริง (tables/charts); confirm ลบ 5 จุดมีชื่อ;
  Enter ส่งฟอร์ม (D2.1); hover states (A4 verify); Thai copy diff (D2.3)

## Risks / Rollback

- สีปุ่มเปลี่ยนทั้งแอป (ตั้งใจ) — ไม่มี visual snapshot จะแตก; rollback = revert PR-1
- จุด override PrimeVue theme ปักแล้ว (main.ts preset) — เหลือความเสี่ยง typecheck
  shape; fallback: CSS override ใต้ `:root` (บันทึกใน Deviations ถ้าใช้)
- Dialog Phase 9 churn หลายไฟล์แต่ mechanical — รีวิวทีละไฟล์, rollback ราย PR
- Phase C เปลี่ยน layout ตารางทุกหน้า — จุดเสี่ยงสุด ต้องรีวิว shots 360px ทุก route
- Phase F เพิ่ม CI minutes — ประเมินก่อนเปิด (optional)
- ทุก phase แยก PR/revert อิสระ; ห้ามยุบรวมตอน publish โดยไม่แจ้งก่อน

## Open Questions

- DTCG pack: adopt หรือ remove? (ไม่ขวางงาน — อยู่นอก scope)
- Jargon scope-out: ขอ sign-off จาก PRD owner (R6 ระบุไว้แต่ T-D2.3 scope ออก)

## Corrections (v1 → v2 — ทุกข้อ verify ด้วย grep/read แล้ว)

- เฉดนอกธีมอยู่ 4 ไฟล์ (เพิ่ม ApprovalChainPanel.vue:58 text-primary-200 ใน script,
  :135 text-primary-300) เฉด 200/300/900 — v2 เขียน "3 ไฟล์ ไม่มี 200" ผิด (round-2)
- `contrast.py` → `python3 scripts/contrast.py` (CLI + exit code จาก docstring)
- จุด override PrimeVue ปักแล้ว: `HrBudgetPreset` + `components.message...info.color`
- DataTables = 24 ไฟล์ / 29 จุด (เพิ่ม CategoryItemsPanel.vue:133; v2 นับ list
  ตัวเอง 28 จุดเป็น 27 ผิด) + native 1 จุด; ItemEditor ห่อแล้ว
- app-dark แปะที่ `<html>` แล้ว (index.html:2, dark active) — v2 GOTCHA กลับหัว
  (round-2); คง override ทั้ง 2 schemes ไว้ (defense in depth)
- Button มี prop `as` (button/index.d.ts:136) — v2 GOTCHA อ้างว่าไม่มี ผิด (round-2);
  คงวิธี anchor แต่ง class (link semantics)
- sky labels: #075985 = sky-800 (v2 เขียน sky-700), #0c4a6e = sky-900 (round-2)
- tokens.spec import .js ตรงพัง verify (TS7016, strict/no-allowJs) — ต้องมี
  `// @ts-expect-error` (round-2)
- เลือก `.table-scroll` class แล้ว (D3 refined) — ไม่เหลือ design ให้ implementer
- QueryErrorState ไม่มี Retry — ต้องขยายก่อน (D7)
- เพิ่ม token primary-700 สำหรับ hover (D6)
- audit spec มี `expect.soft` ที่ :84 อยู่แล้ว — v2r5 เขียน "expect = 0" ผิดเพราะ
  grep `expect(` ไม่มีวันเจอ `expect.soft(` (round-6); T-F1 อัปเกรดของเดิมแทน
- Title ชนะ h1 login (D5); favicon DROP เหลือ verify-only — `/favicon.svg`
  ถูก rebase ให้อยู่แล้ว, `%BASE_URL%` ไม่มี substitution (round-6)
- p-in-button มีจริง 2 บล็อก (Bell :131–146, List :99–122) — v2r3 เขียน "ไม่พบ"
  ผิดเพราะเช็ค parent ไม่ครบ (round-4); แก้ตรงเป็น span + locator ปิดท้าย
- checkboxes :226/233/236/261 ห่อ label อยู่แล้ว → verify-only ห้าม double-wrap
- icons 43 จุด (รวม :264 class-not-first) + widened grep (round-4)
- T-D2.1: ส่งอนุมัติ type=submit + form handler; ร่าง type=button (round-4)
- responsive-layout ยืนยัน dead (grep ใน primevue ได้ 0) — ลบได้ (round-4)
- request-tab h2/empty มีแล้ว (verify-only); ช่องว่างจริงคือ Comparison/Forecast
- `'1-12'` อยู่ที่ PositionListPage:71/:208 (zod ดิบ → แก้เป็นไทย) + :515 (label
  ชัดเจนแล้ว คงไว้) — v2r2 เขียน "grep ไม่พบ" ผิดเพราะ `head` ตัดผล (round-3)
- Files table ชี้ :137/:174 ผิดไฟล์ — ย้ายไป RequestDetailPage แล้ว (round-3)
- T-C1 header "28" → 29; Metadata "6 PRs" → 7 (PR-4a/4b นับแยก) (round-3)
- `'—'` placeholder ถูกแล้ว (คงไว้) — copy sweep มี grep + 82 จุด + close criterion
- icons tally 41 เติมใหม่ + 2 เดิมในเซ็ต (+ 2 นอกเซ็ต multi-line); AppLayout 29
  (v2r4 เขียน 39+4 ผิด) (round-5)
- RoleListPage spans :222–:223 (v2r4 อ้าง :224 ซึ่งเป็น `</div>`) (round-5)
- T-A2 VALIDATE ยืนยันชัด: ไม่มี fails จาก PrimeVue primary controls (round-5)

## Deviations

(บันทึกตอน implement: เปลี่ยนอะไร/ทำไม/หลักฐาน)

- T0.1 baseline (31 routes, mock): serious/critical nodes = 15 (color-contrast 4,
  link-name 6, label 4, target-size 1 — ทั้ง 15 nodes เป็น serious/critical),
  contrast fails = 5 (engine, ทั้งหมด ratio 4.1 ต้องการ 4.5), error lines = 33,
  overflow360 = 26 หน้า (สูงสุด /positions +573px). ไฟล์: `test-results/audit/`
  (31 JSON + summary.json) และสำเนาถาวร `test-results-baseline/` (62 shots).
- T0.1 วิธีเก็บ: spec เป็น serial mode — expect.soft แดงที่ route แรกแล้ว routes
  ที่เหลือ "did not run" + Playwright ล้าง outputDir ทุกครั้งที่รัน จึงรันราย route
  (`-g "audit <route>$" — anchor `$` กัน /requests ชน /requests/1|create|edit)
  แล้วสำรอง JSON/shots ทันทีหลังแต่ละรัน; summary.json รวมด้วย node logic เดียวกับ
  spec (lines 90–106) แทนการรัน 'audit summary' test (รันเปล่าได้ 0 routes เพราะ
  ถูกล้างก่อนอ่าน).
- T0.1 env repair: `npm i -D @rollup/rollup-linux-x64-gnu` ใน frontend/
  (vite สตาร์ทไม่ขึ้น: npm optional-deps bug) + `npm i` ที่ root (axe-core หาย) +
  `npx playwright install chromium`. เพิ่ม devDep 1 บรรทัดใน frontend/package.json
  (harmless บนทุก platform) — ถือเป็น env fix ไม่ใช่ scope creep.
- T0.1 สังเกต: axe มี run-to-run variance (รอบ 1 `/requests/1` axe=0, รอบ 2 axe=2
  link-name) — เทียบ phase ถัดไปให้ดูแนวโน้ม ไม่ยึดเลข route เดี่ยว.
- T-A1 เพิ่ม: `FileUploader.vue:121` (hover:bg-primary-500 → 700) — ไม่อยู่ใน list
  7 จุดของแผน แต่เป็น pattern เดียวกัน (ปุ่มพื้นทึบ primary-600 + ข้อความขาว)
  ตามเจตนา D6 "hover พื้นทึบทั้งหมด"; verify `hover:bg-primary-500` = 0 ทั้ง src.
- T-A1 หมายเหตุ: repo เป็น CRLF — muse.edit_file ใช้กับ multi-line find ไม่ได้
  (exact-match fail) ต้องแก้ผ่าน python (newline='') หรือ sed; single-line find
  ยังใช้ได้ปกติ.
- T-A3 drift: LoginPage:155 base เป็น text-primary-400 อยู่แล้ว (แผนเขียน 300) —
  remap เฉพาะ hover/focus-visible 300→400 + เติม hover:underline ตามเจตนาแผน.
- T-A4 verdict: `hover:text-primary-500` ข้อความ (7 จุด รวม FileUploader:138 +
  NotificationBell:114/:152 ที่นอก list) วัดแล้ว #0ea5e9 บน #0f172a = 6.44:1,
  บน #1e293b = 5.28:1 — ผ่าน AA จึงคงไว้ทั้งหมด ไม่ remap (ตรงเงื่อนไข
  "ถ้าโผล่ใน fails ค่อยชี้เป็น primary-400").
- T-B1  lesson: dev server บน /mnt (DrvFs) ไม่รับ file-watch events — แก้ไฟล์แล้ว
  audit ยังเจอของเก่า (stale transform) ต้อง restart server (fuser -k 5174/tcp;
  ห้าม pkill -f "vite --host" เพราะ pattern ตรงกับ shell ตัวเอง → SIGTERM).
  ทุก task ที่ต้องยืนยันด้วย audit หลังแก้ไฟล์: restart dev server ก่อนรัน audit
  เสมอ. และทุกครั้งที่รัน audit >1 route: สำรอง JSON ทันทีหลังแต่ละรัน
  (Playwright ล้าง outputDir ทุก invocation).
- T-B2 สรุป: wrappers 6+5+5+5+2 + rates 7 = 30 FormField ใหม่ (5 ไฟล์, ไม่มี
  :error — ทั้ง 5 ฟอร์มไม่มี errors object); checkboxes verify-only ถูกต้อง
  (ห่อ `<label>` แล้วทั้ง 3 กลุ่ม — ไม่แตะ); audit 5 หน้า axe=0 label=0.
  กับดักสคริปต์: Select บรรทัดเดียวได้ attribute หลุดนอก tag (เจอที่ Policy 2 จุด —
  แก้เป็น multi-line แล้ว); Select ที่ v-model อยู่คนละบรรทัดกับ `<Select`
  หลุด matcher (at-derive — ห่อมือแล้ว).
- T-B3 verify-first: dialog Position :441–542 มี FormField + id ครบทุก control
  อยู่แล้ว — ไม่แตะ (ตรงแผน).
- T-B4 เพิ่มจากแผน: skip link ใช้ `@click.prevent="focusMain"` + `main
  tabindex="-1"` เพราะ plain `href="#main"` ถูก vue-router สกัด (Enter แล้วโฟกัส
  ค้างที่ BODY); probe ยืนยัน Tab→skip→Enter→MAIN#main แล้ว. Escape ปิด
  drawer มีอยู่แล้ว (useEscapeClose :48) — T-B4 เติมแค่ focus-in/trap/return-focus.
- T-B4 test fix: synthetic KeyboardEvent ต้องมี `cancelable: true` ไม่เช่นนั้น
  preventDefault เป็น no-op (defaultPrevented false) — ไม่ใช่บั๊ก implementation.
- T-B5 เปลี่ยนวิธี: plain `<a class="p-button...">` ไม่ได้ PrimeVue CSS เลย
  (v4 โหลด component CSS ต่อเมื่อมี component instance บนหน้านั้น — probe ได้
  display:block/transparent) จึงใช้ `<Button as="a" :href ...>` แทน: ได้ element
  เดียว + link semantics + สไตล์/disabled ครบ (computed bg rgb(74,222,128)
  เขียว success ตรง baseline, shot /tmp/export-as.png); เก็บ aria-disabled +
  pointer-events-none เมื่อ !hasData ไว้คุม disabled เอง.
- T-B5 ระวัง: audit shot หน้าว่างหลัง restart (server เย็น compile ไม่ทัน 1500ms)
  ทำให้ axe=0 ปลอม — audit ใหม่ตอน server อุ่นแล้วเท่านั้น.
- T-B5 ค้าง: /vault target-size x1 (`button[pc9=""]` ปุ่มลบไฟล์ icon-only 22px)
  เป็น node เดิมจาก baseline ไม่ใช่ breadcrumb (breadcrumb ได้ min-h-6 แล้ว) —
  นอก scope T-B5, ไม่แตะ.
- T-B6 tally: widened grep 43 จุด — เติมใหม่ 39 + เดิมในเซ็ต 3 (Login :142/:143,
  Export :118 จาก T-B5) + นอกเซ็ต 2 (:102/:122 multi-line มีอยู่แล้ว) = มี
  aria-hidden ครบ 43/43 (verify ด้วยสคริปต์ไม่ใช่ตาเปล่า). ข้อควรระวัง:
  `RequestApprovalChart.vue` เป็น LF-only (ไฟล์เดียว) — สคริปต์ split CRLF
  พัง ต้องจัดการแยกและคง LF ไว้.
- T-B6 เหลือ: `<div>` ใน `<button>` (Bell + List) ยังอยู่ — แผนสั่งแค่ p→span
  (close criterion คือ locator `button p` = 0 ซึ่งผ่านแล้ว) จึงไม่แตะ div;
  เป็น follow-up candidate ถ้าจะทำ HTML-valid เต็ม.
- T-C1 บทเรียนใหญ่: ห้ามห่อ `<DataTable v-else>` ด้วย div เปล่า — ทำลาย
  v-if/v-else adjacency (vite 500 ทั้งหน้า, 19 หน้าแดงพร้อม overlay) ต้องย้าย
  `v-else` ขึ้น wrapper (`<div class="table-scroll" v-else>`, 20 จุด) —
  vue-tsc จับไม่ได้ ต้องเช็คด้วย dev compile/audit เท่านั้น.
- T-A1 แก้เพิ่ม (สำคัญ): `tailwindcss-primeui` สร้าง utilities `*-primary-*`
  จาก `var(--p-primary-*)` ทับค่า tailwind.config — runtime จึงใช้ #0284c7
  อยู่ดี (contrast engine ยังแดง 4.1) ต้อง remap ที่ preset ด้วย:
  `main.ts` 600→`{sky.700}` (#0369a1), 700→`{sky.800}` (#075985) —
  หลังแก้ contrast fails 5→0 ทั้ง 31 routes (verify ใน Phase C).
- Phase C สรุป (31 routes): overflow 26 หน้า→0, contrast 5→0, axe 15→3 nodes
  (เหลือ link-name x2 ที่ /requests/1/edit — ตระกูล flaky เดียวกับ baseline —
  กับ target-size x1 ของ vault ที่เป็น node เดิม).
- T-D1.3 probe: mock request-detail ไม่มี `request_status` — หน้า edit ถูก guard
  redirect ตลอด ต้อง fulfill เอง (`request_status:'draft'`) ถึงจะ probe ได้;
  PrimeVue v4 dialog selector คือ `.p-confirmdialog` (ไม่มี dash — `.p-confirm-dialog`
  ได้ 0 เสมอ). M6 ผ่าน: pristine→ไปเลย, dirty→confirm, Escape/ทำต่อ→อยู่ต่อ.
- T-D1.5 titles 9 จุด (dynamic :title ผูกเงื่อนไขเดียวกับ :disabled — hover ตอน
  enabled ไม่เห็น hint ผิด); runtime-verify 7 (compute×2, vault, export, wiz
  step1, allowance toast-path); static-verify 2 (role toggle — mock ไม่มี
  system row; rate add — อยู่ใน rates dialog ลึก, pattern เดียวกับที่ verify).
- T-D2.3 tally: visible ' — ' → ' – ' 22 บรรทัด (รวม h2 wizard, subtitle,
  date-range, toast/tooltip ใหม่ของ T-D1.4/5) + zod '1-12' → 'ต้องเป็นตัวเลข
  1–12' (2 schemas); คง '—' เฉพาะ placeholder `?? '—'`/`<span v-else>—`
  (29 จุด), คอมเมนต์, และ Compute:172 ที่เป็น – อยู่แล้ว. ระวัง line drift
  จาก T-B2 — ยึดข้อความจริงไม่ใช่เลขบรรทัดในแผน.
- T-E1 แก้วิธี: ห่อ div role=img ทำให้ SR เจอ img ซ้ำ (wrapper + canvas ของ
  chart.js ที่ได้ role อัตโนมัติแต่ไม่มีชื่อ) — ใช้ prop `ariaLabel` ของ
  vue-chartjs แทน (`<Bar :aria-label="title">` → canvas named) แล้วเอา role
  ออกจาก wrapper; เหลือ CANVAS named 1:1 ต่อชาร์ต, pageerror 0. ForecastChart
  นอก scope (ไม่มีในแผน) — ไม่แตะ.
- T-E1 probe: mock มาตรฐานให้ charts ว่างเสมอ (years/list shape) — ต้อง fulfill
  `/budget-execution/years` + report เอง (route ที่ลงทะเบียนก่อนชนะ — mock
  ของ audit ลงก่อนจึงต้องเขียน mock เองทั้งก้อน ไม่ใช่ override ทีหลัง).
