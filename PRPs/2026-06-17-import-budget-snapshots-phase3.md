# PRD — Import Budget-Execution Snapshots (Phase 3)

- **Date:** 2026-06-17
- **Branch:** `feat/import-budget-snapshots-phase3`
- **Depends on:** Phase 1 (RBAC/scope), Phase 2 (org tree)

## Problem
The 36 cumulative budget-execution PDFs (`docs/documents/สะสม/`) hold per-unit,
per-date "รวมงบประมาณทั้งสิ้น" figures — the real disbursement history of สป.ยธ.
The DB had no populated representation of this time-series.

## Schema reality → design choice
- `budget_allocations.plan_id` is NOT NULL and `budget_monthly_snapshots.allocation_id`
  is NOT NULL — both assume a reliable plan/category breakdown.
- The PDFs reliably yield only the **per-unit grand total** per date, not a clean
  plan/category split (deep-table extraction is noisy).
- **Decision:** extend `budget_monthly_snapshots` to support **organization-level**
  snapshots (migration 069: `allocation_id` nullable, add `organization_id`,
  `allocated_pba`, `transfer`, `pending`, `source`). Import org-level rows; defer
  line-item (plan/category) detail.

## Implementation
- **069_org_level_budget_snapshots.sql** — schema extension + unique
  `(organization_id, fiscal_year, snapshot_date)` for idempotent upsert.
- **python/import_budget_snapshots.py** — for each PDF (= a snapshot date parsed
  from the filename, BE→CE + fiscal-year rule month≥10 ⇒ +1) and each page (= a
  unit), reads the grand-total line, normalizes the unit name (folds the font's
  decomposed SARA-AM), maps unit → organization `code`, and emits idempotent
  `INSERT ... ON DUPLICATE KEY UPDATE`. Org id resolved by `code` in SQL (no Python
  DB driver needed). Reports unmatched units.

## Column mapping (PDF → snapshot)
งบ พรบ. → `allocated_pba` · งบได้รับจัดสรร → `allocated_received` · โอน → `transfer` ·
เบิกจ่าย → `disbursed` · ขออนุมัติวงเงิน → `pending` · PO → `po_commitment` · คงเหลือ → `remaining`

## Verification
- Apply 069 to live DB (done); run importer → apply generated SQL; assert snapshot
  rows > 0, distinct orgs, distinct dates span FY2568–2569, spot-check one unit
  (e.g. OPS-HR) across dates shows rising disbursement.

## Scope / deferred
- Line-item (plan/category/activity) detail import → future.
- `category`/`region` scope **enforcement** still deferred (org-level snapshots
  carry no category dimension); org scope works via `organization_id`.
- CI note: importer is a Python data script + 069 is a MySQL-only ALTER; neither is
  exercised by CI (SQLite unit tests / e2e from `hr_budget_only.sql`), so the 321
  PHP unit tests stay green. Import is verified against the live DB.

## Next (Phase 4)
Multi-step approval chain (กอง→กรม→กระทรวง) using the approver_* roles from Phase 1.
