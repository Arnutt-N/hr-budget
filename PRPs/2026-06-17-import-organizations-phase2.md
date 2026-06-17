# PRD — Import Real Organizations (Phase 2)

- **Date:** 2026-06-17
- **Branch:** `feat/import-organizations-phase2`
- **Status:** Approved → implemented
- **Depends on:** Phase 1 RBAC (PR #25, merged) — org tree powers hierarchical scoping

## Problem
`organizations` held only 5 auto-generated placeholder rows (codes `MN-/DP-/DV-/SC-`).
Multi-org scoping (Phase 1) and the upcoming budget import (Phase 3) need the real
units of **สำนักงานปลัดกระทรวงยุติธรรม**, taken from the official budget PDFs.

## Scope
- Seed the real org tree (migration `068_seed_real_organizations.sql`).
- Deactivate the 5 placeholders (NOT delete — `disbursement_sessions` FK references them).
- **Out of scope:** provincial justice offices (สำนักงานยุติธรรมจังหวัด, ~81) — separate
  dataset coordinated by กองประสานราชการยุติธรรมจังหวัด; deferred.

## Tree (25 active orgs)
```
กระทรวงยุติธรรม (MOJ, ministry, L0)
├─ สำนักงานปลัดกระทรวงยุติธรรม (MOJ-OPS, department, L1)
│  ├─ กองยุทธศาสตร์และแผนงาน (OPS-STRAT, division, L2)
│  │  ├─ (บริหารส่วนกลาง) (OPS-STRAT-CENTRAL, section, L3)
│  │  └─ ส่วนนโยบายและยุทธศาสตร์ จชต. (OPS-STRAT-SBPAC, section, L3)
│  ├─ กองประสานราชการยุติธรรมจังหวัด (OPS-PROV)
│  ├─ สำนักงานส่งเสริมสัมมาชีพฯ (OPS-VOC)
│  ├─ ศูนย์บริการร่วม (OPS-SC)
│  ├─ กองการต่างประเทศ (OPS-INTL)
│  ├─ สถาบันพัฒนาบุคลากรกระทรวงยุติธรรม (OPS-HRD)
│  ├─ กองกฎหมาย (OPS-LAW) · กองกลาง (OPS-CENTRAL) · สำนักงานรัฐมนตรี (OPS-MIN)
│  ├─ กลุ่มตรวจสอบภายใน (OPS-AUDIT) · สำนักผู้ตรวจราชการฯ (OPS-INSP)
│  ├─ กองบริหารทรัพยากรบุคคล (OPS-HR) · กองออกแบบและก่อสร้าง (OPS-CONS)
│  ├─ ศูนย์เทคโนโลยีสารสนเทศและการสื่อสาร (OPS-ICT)
│  ├─ กองบริหารการคลัง (OPS-FIN) ─ (ค่าใช้จ่ายส่วนกลาง) (OPS-FIN-CENTRAL, section)
│  ├─ กลุ่มพัฒนาระบบบริหารฯ (OPS-PSDG) · ศูนย์ปฏิบัติการต่อต้านการทุจริตฯ (OPS-ACT)
│  ├─ กองพัฒนานวัตกรรมการยุติธรรม (OPS-INNO) · กลุ่มภารกิจพัฒนาพฤตินิสัย (OPS-REHAB)
└─ เนติบัณฑิตยสภา/สภาทนายความ (MOJ-EXT-LAW, office, L1 — external recipient)
```

## Design notes
- Names verbatim from the PDFs (SARA-AM ligatures normalized to standard Thai).
- Codes are systematic placeholders (`MOJ`, `MOJ-OPS`, `OPS-*`) — remap to official
  codes later without structural change.
- `parent_id` resolved by `code` (portable, idempotent `INSERT IGNORE`).
- org_type maps กอง/ศูนย์/สำนัก/กลุ่ม/สถาบัน → `division`; ส่วน/cost-centre → `section`;
  external → `office`.

## Verification
- ✅ Applied to live DB: 1 ministry / 1 dept / 19 div / 3 section / 1 office = 25 active.
- ✅ Recursive CTE (same one AccessScopeResolver uses): descendants(MOJ)=24, subtree(OPS-STRAT)=3.
- ✅ Placeholders deactivated (is_active=0), FK integrity preserved.
- Note: seed is verified against the live DB. CI (SQLite unit tests + e2e from
  `hr_budget_only.sql`) does not run migrations, so it is unaffected (321 unit tests stay green).

## Next (Phase 3)
Import budget-execution data (36 PDFs + Excel) → `budget_allocations` +
`budget_monthly_snapshots`, mapping each unit to its `organization_id`, and wire
`category`/`region` scope enforcement.
