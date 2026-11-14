<?php
declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Core\Database;
use App\Repositories\BudgetRequestRepository;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Regression guard for updateWhereStatus()'s field whitelist.
 *
 * submit() writes ['request_status', 'current_level', 'submitted_at'] through
 * this method. The whitelist silently dropped any key it did not name, so a
 * missing 'current_level' meant requests entered the chain at level NULL and
 * every approval returned not_in_chain. Stub repos cannot catch this because
 * they bypass the whitelist entirely.
 */
final class BudgetRequestRepositoryTest extends TestCase
{
    private PDO $pdo;
    private BudgetRequestRepository $repo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);
        $this->pdo->exec(
            "CREATE TABLE budget_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER, request_title TEXT,
                request_status TEXT DEFAULT 'draft', current_level INTEGER,
                total_amount REAL DEFAULT 0, created_by INTEGER, org_id INTEGER,
                submitted_at TEXT, approved_at TEXT, rejected_at TEXT,
                rejected_reason TEXT
             )"
        );
        $this->repo = new BudgetRequestRepository();
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function makeRequest(string $status = 'draft'): int
    {
        Database::insert('budget_requests', [
            'fiscal_year' => 2570,
            'request_title' => 'คำขอทดสอบ',
            'request_status' => $status,
            'total_amount' => 1000,
            'created_by' => 1,
            'org_id' => 1,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @test */
    public function update_where_status_persists_current_level_and_submitted_at(): void
    {
        $id = $this->makeRequest('draft');

        $ok = $this->repo->updateWhereStatus($id, 'draft', [
            'request_status' => 'pending',
            'current_level' => 1,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);

        $this->assertTrue($ok);
        $row = Database::queryOne('SELECT * FROM budget_requests WHERE id = ?', [$id]);
        $this->assertSame('pending', $row['request_status']);
        $this->assertSame(1, (int) $row['current_level'], 'submit must place the request at chain level 1');
        $this->assertNotNull($row['submitted_at']);
    }

    /** @test */
    public function update_where_status_ignores_unknown_fields(): void
    {
        $id = $this->makeRequest('draft');

        $ok = $this->repo->updateWhereStatus($id, 'draft', [
            'request_status' => 'pending',
            'org_id' => 999,
        ]);

        $this->assertTrue($ok);
        $row = Database::queryOne('SELECT * FROM budget_requests WHERE id = ?', [$id]);
        $this->assertSame(1, (int) $row['org_id'], 'whitelist must not apply columns it does not name');
    }
}