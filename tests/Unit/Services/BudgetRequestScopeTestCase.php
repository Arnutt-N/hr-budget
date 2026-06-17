<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;

/**
 * RBAC SQLite fixture + a budget_requests table shaped like the columns the
 * BudgetRequestRepository reads (br.* + created_at for ORDER BY, joined to
 * users.name and organizations.name_th).
 */
abstract class BudgetRequestScopeTestCase extends RbacSqliteTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo->exec(
            "CREATE TABLE budget_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER,
                request_title TEXT,
                request_status TEXT DEFAULT 'draft',
                total_amount REAL DEFAULT 0,
                created_by INTEGER,
                org_id INTEGER,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
             );"
        );
    }

    protected function makeRequest(int $orgId, int $createdBy, string $status = 'pending'): int
    {
        return Database::insert('budget_requests', [
            'fiscal_year' => 2569,
            'request_title' => 'คำขอทดสอบ',
            'request_status' => $status,
            'total_amount' => 1000,
            'created_by' => $createdBy,
            'org_id' => $orgId,
        ]);
    }
}
