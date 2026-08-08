-- =====================================================
-- HR Budget System - Drop Dimensional Tables
-- Version: 1.0
-- Date: 2025-12-22
-- Reason: ข้อมูลเป็น mock-up ทั้งหมด ไม่มีข้อมูลจริง
-- =====================================================

-- Drop Views first
DROP VIEW IF EXISTS v_fact_summary_by_year;
DROP VIEW IF EXISTS v_structure_with_execution;

-- Drop tables in correct order (child tables first)
DROP TABLE IF EXISTS log_transfer_note;
DROP TABLE IF EXISTS fact_budget_execution;
DROP TABLE IF EXISTS dim_budget_structure;
DROP TABLE IF EXISTS dim_organization;

SELECT 'Dimensional tables dropped successfully' AS status;
