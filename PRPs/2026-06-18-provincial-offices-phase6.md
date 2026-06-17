# PRP — Phase 6: 81 สำนักงานยุติธรรมจังหวัด into the org tree

**Date:** 2026-06-18
**Branch:** `feat/provincial-offices-phase6`
**Depends on:** Phase 2 (org tree, PR #26) + Phase 1 (subtree scoping, PR #25).

## 1. Problem / Goal

The org tree (Phase 2) covers the ministry → สป.ยธ. → 19 central divisions, but the
provincial layer — the **สำนักงานยุติธรรมจังหวัด** that `กองประสานราชการยุติธรรมจังหวัด`
coordinates — is missing. Without it, RBAC **region scope is meaningless** (no provinces to
scope to) and provincial budgets cannot be attributed. Phase 6 seeds the provincial offices.

## 2. Key research findings (live DB)

- Parent node exists: **`OPS-PROV` (id 9, level 2)** = "กองประสานราชการยุติธรรมจังหวัด" — the
  correct anchor for provincial offices.
- `organizations` already supports it: `org_type` enum includes `province` + `office`,
  plus `province_code`, `parent_id` (self-FK), `region`. **No schema change needed.**
- `organizations.region` is an **admin classification** enum
  (`central`/`regional`/`provincial`/`central_in_region`) — NOT geographic. So geographic
  grouping must come from the **tree shape**, not this column.
- `organizations.code` has a UNIQUE index → `INSERT IGNORE` is idempotent.
- `provinces` reference table exists but is effectively empty (1 placeholder row) — not
  relied on here; populating it is a separate follow-up.

## 3. Approach / Architecture

**Make region scope meaningful via the tree, reusing Phase 1's recursive-CTE subtree
resolution** (no new region-scope machinery):

```
OPS-PROV (กองประสานราชการยุติธรรมจังหวัด)         [L2, existing]
├── PROV-RGN-N  ภาคเหนือ            [L3 division]
│   ├── JP-50 สำนักงานยุติธรรมจังหวัดเชียงใหม่   [L4 province]
│   │   └── JP-57-THOENG … สาขา…              [L5 office]   (where applicable)
│   └── …
├── PROV-RGN-NE ภาคตะวันออกเฉียงเหนือ
├── PROV-RGN-C  ภาคกลาง
├── PROV-RGN-E  ภาคตะวันออก
├── PROV-RGN-W  ภาคตะวันตก
└── PROV-RGN-S  ภาคใต้
```

A "ผู้ดูแลภาค" granted **organization** scope on a `PROV-RGN-*` node then sees every province
(and สาขา) under it — the existing `AccessScopeResolver` subtree CTE handles arbitrary depth,
so **no code change** is required.

**Composition of 81 offices** (76 provinces ex-Bangkok + 5 branch offices):
- 76 main `สำนักงานยุติธรรมจังหวัด`, one per province, grouped under the 6 standard NGC
  geographic regions (เหนือ 9 / อีสาน 20 / กลาง 21 / ออก 7 / ตก 5 / ใต้ 14 = 76).
- 5 documented `สาขา` under their parent province office: นางรอง (บุรีรัมย์), เทิง (เชียงราย),
  เบตง (ยะลา), หล่มสัก (เพชรบูรณ์), ทองผาภูมิ (กาญจนบุรี).
- `province_code` = the standard 2-digit Thai geocode; `region` = `'provincial'`.

Code scheme: region `PROV-RGN-*`; province `JP-<geocode>`; branch `JP-<geocode>-<NAME>`.

Deliverables: `database/migrations/071_seed_provincial_offices.sql` (idempotent
`INSERT IGNORE … SELECT` resolving parents by code) + `071_rollback.sql`.

## 4. Scope (out / deferred)

- Populating the `provinces` reference table (76 rows + geographic region) — separate task.
- Attributing real provincial budgets / line items to these offices.
- Bangkok is intentionally excluded (no สำนักงานยุติธรรม**จังหวัด**; served centrally).
- The exact branch-office (สาขา) roster may differ from the official current list; the
  migration is idempotent and easy to amend if HR provides the authoritative set.

## 5. Testing / Acceptance

- Apply 071 to the live DB; verify: +6 region nodes, +76 provinces, +5 สาขา = **+87 orgs**;
  every province's `parent_id` resolves to a `PROV-RGN-*` node; every สาขา's parent is its
  province; re-running the migration adds 0 rows (idempotent).
- Verify subtree scoping: `AccessScopeResolver` expandDescendants on a `PROV-RGN-*` id returns
  the region's provinces + their branches (covered by the existing recursive-CTE unit tests;
  add a focused L3→L5 depth assertion).
- CI unaffected: migrations are not run in CI (it seeds from `hr_budget_only.sql`); no PHP/TS
  code changes → PHP, Frontend, E2E jobs stay green.

## 6. Risks

- **Region assignment ambiguity** (e.g. ตาก west vs central, เพชรบูรณ์ central vs north):
  resolved by following the ราชบัณฑิตยสภา (NGC) 6-region standard exactly.
- **สาขา roster drift**: mitigated by idempotent INSERT IGNORE + documented composition.
