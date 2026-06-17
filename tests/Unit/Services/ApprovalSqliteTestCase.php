<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;

/**
 * Extends the RBAC SQLite fixture with the approval-chain tables + approver roles
 * and the 3-level chain (division → department → ministry).
 */
abstract class ApprovalSqliteTestCase extends RbacSqliteTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo->exec(
            "CREATE TABLE approval_levels (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                level INTEGER UNIQUE, code TEXT UNIQUE, name_th TEXT,
                role_code TEXT, is_active INTEGER DEFAULT 1
             );
             CREATE TABLE budget_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER, request_title TEXT,
                request_status TEXT DEFAULT 'draft', current_level INTEGER,
                total_amount REAL DEFAULT 0, created_by INTEGER, org_id INTEGER
             );
             CREATE TABLE budget_request_approvals (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                budget_request_id INTEGER, action TEXT, level INTEGER,
                user_id INTEGER, note TEXT, created_at TEXT DEFAULT CURRENT_TIMESTAMP
             );"
        );

        // approver roles (parent seeds the read/edit roles; add the chain roles)
        $this->pdo->exec(
            "INSERT INTO roles (code, name_th, is_system) VALUES
                ('approver_division','ผู้อนุมัติระดับกอง',0),
                ('approver_department','ผู้อนุมัติระดับกรม',0),
                ('approver_ministry','ผู้อนุมัติระดับกระทรวง',0);
             INSERT INTO role_permissions (role_id, permission_id)
                SELECT r.id, p.id FROM roles r, permissions p
                WHERE r.code IN ('approver_division','approver_department','approver_ministry')
                  AND p.code IN ('request.view','request.approve');
             INSERT INTO approval_levels (level, code, name_th, role_code) VALUES
                (1,'division','อนุมัติระดับกอง','approver_division'),
                (2,'department','อนุมัติระดับกรม','approver_department'),
                (3,'ministry','อนุมัติระดับกระทรวง','approver_ministry');"
        );
    }

    protected function makeRequest(?int $orgId, string $status = 'pending', ?int $level = 1): int
    {
        return Database::insert('budget_requests', [
            'fiscal_year' => 2569,
            'request_title' => 'คำขอทดสอบ',
            'request_status' => $status,
            'current_level' => $level,
            'total_amount' => 1000,
            'created_by' => 1,
            'org_id' => $orgId,
        ]);
    }
}
