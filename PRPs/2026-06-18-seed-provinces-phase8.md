# PRP — Phase 8: populate the `provinces` reference table

**Date:** 2026-06-18
**Branch:** `feat/seed-provinces-phase8`
**Depends on:** Phase 6 (org tree provincial offices, PR #30).

## 1. Problem / Goal

Phase 6 set `organizations.province_code` to standard 2-digit Thai geocodes for the 81
provincial offices, but the `provinces` reference table held only 1 placeholder row (Bangkok,
random code `PR-f9f6`). So `province_code` joined to nothing and the **geographic** region of
each office was unavailable. Phase 8 populates `provinces` with all 77 provinces keyed by
geocode, so `organizations.province_code = provinces.code` resolves and
`provinces.region` (central/north/northeast/east/west/south) carries the geographic region.

## 2. Approach

Pure data migration (`072_seed_provinces.sql`); `provinces.code` is UNIQUE → idempotent.

- **UPDATE** the existing Bangkok placeholder: `code → '10'`, `name_en='Bangkok'`,
  `region='central'`.
- **INSERT IGNORE** the other 76 provinces: geocode `code`, `name_th`, `name_en`
  (RTGS romanization), geographic `region`, `sort_order`=geocode.

Region grouping follows the ราชบัณฑิตยสภา / NGC 6-region standard and **matches the
PROV-RGN-\* org grouping from Phase 6** (verified: ตาก→west, เพชรบูรณ์→central, etc.), so the
two layers are consistent.

`provinces.region` (geographic, 6-region enum) is intentionally distinct from
`organizations.region` (admin classification: central/regional/provincial/central_in_region).

## 3. Scope (out)

- No schema change; no code change (the table already existed, migration 032).
- `province_group_id` / `province_zone_id` / `inspection_zone_id` left null (separate
  grouping dimensions, not needed here).
- Converting `organizations.province_code` into a real FK to `provinces` — deferred (it stays
  a denormalized geocode tag; the JOIN works on `code`).

## 4. Testing / Acceptance (verified on live DB)

- 1 → 77 rows; re-running adds 0 (idempotent).
- Bangkok re-coded to `10` / `central`.
- Region counts: central 22, northeast 20, south 14, north 9, east 7, west 5 = 77.
- **JOIN check: 0** of the 76 `JP-*` org province_codes are left unmatched against
  `provinces.code` — the Phase 6 ↔ Phase 8 link is complete.
- CI unaffected (migrations are not run in CI; no code change). Shipped via PR with CI paused
  (GitHub Actions quota); verified locally.

## 5. Risks

- Region edge cases (ตาก, เพชรบูรณ์) — resolved by following NGC and matching Phase 6.
- `name_en` romanizations are standard RTGS; non-critical (nullable reference field).
