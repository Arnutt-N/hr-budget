-- Rollback 074_align_province_name_en.sql
-- Restores the "Buri as a separate word" spelling that migration 072 seeded.
-- Nothing depends on name_en (it is display-only), so this is always safe.
UPDATE provinces SET name_en = 'Nontha Buri'   WHERE code = '12';
UPDATE provinces SET name_en = 'Sara Buri'     WHERE code = '19';
UPDATE provinces SET name_en = 'Chantha Buri'  WHERE code = '22';
UPDATE provinces SET name_en = 'Ratcha Buri'   WHERE code = '70';
UPDATE provinces SET name_en = 'Kanchana Buri' WHERE code = '71';
UPDATE provinces SET name_en = 'Phetcha Buri'  WHERE code = '76';
UPDATE provinces SET name_en = 'Phang Nga'     WHERE code = '82';
