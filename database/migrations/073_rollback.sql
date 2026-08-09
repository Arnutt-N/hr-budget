-- Rollback 073_recode_branch_offices.sql
-- Restores the original romanised branch codes. Run this BEFORE 071_rollback
-- if you are unwinding Phase 6/8 completely — 071_rollback also matches the
-- new JP-<4 digit> form, but restoring the old codes first keeps the two
-- rollbacks independent.
-- If 075 has been applied, run 075_rollback first: organizations.district_code
-- is derived from these codes and would be left pointing at a dropped table.
UPDATE organizations SET code = 'JP-31-NANGRONG'      WHERE code = 'JP-3104';
UPDATE organizations SET code = 'JP-57-THOENG'        WHERE code = 'JP-5704';
UPDATE organizations SET code = 'JP-95-BETONG'        WHERE code = 'JP-9502';
UPDATE organizations SET code = 'JP-67-LOMSAK'        WHERE code = 'JP-6703';
UPDATE organizations SET code = 'JP-71-THONGPHAPHUM'  WHERE code = 'JP-7107';
