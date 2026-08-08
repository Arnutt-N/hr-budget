-- Migration: Create budget_records table
-- Version: 007
-- Description: บันทึกข้อมูลรายเดือนสำหรับแต่ละงบประมาณ

CREATE TABLE IF NOT EXISTS budget_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    budget_id INT NOT NULL,
    record_date DATE NOT NULL COMMENT 'วันที่บันทึก',
    record_period ENUM('beginning', 'mid', 'end') DEFAULT 'beginning' COMMENT 'ช่วงเวลา: ต้นเดือน/กลางเดือน/ปลายเดือน',
    
    -- ข้อมูลที่บันทึก
    transfer_allocation DECIMAL(15,2) DEFAULT 0 COMMENT 'โอนจัดสรร/โอนเบิกแทน/โอนเปลี่ยนแปลง',
    spent_amount DECIMAL(15,2) DEFAULT 0 COMMENT 'เบิกจ่าย',
    request_amount DECIMAL(15,2) DEFAULT 0 COMMENT 'ขออนุมัติวงเงิน',
    po_amount DECIMAL(15,2) DEFAULT 0 COMMENT 'ก่อหนี้ผูกพัน (PO)',
    
    notes TEXT COMMENT 'หมายเหตุ',
    created_by INT NULL,
    updated_by INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (budget_id) REFERENCES budgets(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_budget_date (budget_id, record_date),
    INDEX idx_record_date (record_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add comment to describe table
ALTER TABLE budget_records COMMENT = 'บันทึกข้อมูลงบประมาณรายเดือน (ต้น/กลาง/ปลายเดือน)';
