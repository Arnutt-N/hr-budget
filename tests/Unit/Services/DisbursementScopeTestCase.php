<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;

/**
 * RBAC SQLite fixture + a disbursement_sessions table shaped like the columns
 * DisbursementSessionRepository reads (ds.* joined to organizations.name_th).
 */
abstract class DisbursementScopeTestCase extends RbacSqliteTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo->exec(
            "CREATE TABLE disbursement_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                organization_id INTEGER,
                fiscal_year INTEGER,
                record_month INTEGER,
                record_date TEXT,
                created_by INTEGER,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT
             );"
        );
    }

    protected function makeSession(int $orgId, int $createdBy, int $month = 1): int
    {
        return Database::insert('disbursement_sessions', [
            'organization_id' => $orgId,
            'fiscal_year' => 2569,
            'record_month' => $month,
            'record_date' => '2026-01-15',
            'created_by' => $createdBy,
        ]);
    }
}
