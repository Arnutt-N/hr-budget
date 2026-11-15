<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Repositories\BudgetCategoryRepository;
use App\Services\BudgetCategoryService;
use PHPUnit\Framework\TestCase;

/**
 * Runs the real repository against an in-memory SQLite DB, so the
 * activeOnly flag is proven to reach the SQL — not just the call signature
 * (a mocked repository would pass even if the WHERE clause were missing).
 */
class BudgetCategorySoftDeleteTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec(
            "CREATE TABLE budget_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT,
                name_th TEXT NOT NULL,
                parent_id INTEGER,
                level INTEGER DEFAULT 0,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1
            );
            CREATE TABLE budget_category_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                category_id INTEGER,
                name TEXT,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT
            );"
        );
        $this->pdo->exec(
            "INSERT INTO budget_categories (id, code, name_th, parent_id, level, sort_order, is_active) VALUES
             (1, 'ROOT', 'Root', NULL, 0, 0, 1),
             (2, 'ACTIVE', 'Active child', 1, 1, 1, 1),
             (3, 'DELETED', 'Deleted child', 1, 1, 2, 0),
             (4, 'GRANDCHILD', 'Child of deleted', 3, 2, 1, 1)"
        );
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    public function testListExcludesSoftDeletedCategory(): void
    {
        $repo = new BudgetCategoryRepository();
        $service = new BudgetCategoryService($repo, $this->createMock(\App\Repositories\BudgetCategoryItemRepository::class));

        $result = $service->list();

        $ids = array_map(static fn ($r) => (int) $r['id'], $result['data']);
        $this->assertContains(1, $ids);
        $this->assertContains(2, $ids);
        $this->assertNotContains(3, $ids);
        // 4 is active even though its parent is filtered out, and the list
        // filter is on the row itself, so it stays visible.
        $this->assertContains(4, $ids);
        $this->assertSame(3, $result['meta']['total']);
    }

    public function testTreeExcludesSoftDeletedCategoryAndItsChildren(): void
    {
        $repo = new BudgetCategoryRepository();
        $service = new BudgetCategoryService($repo, $this->createMock(\App\Repositories\BudgetCategoryItemRepository::class));

        $tree = $service->getTree();

        $root = $tree[0];
        $this->assertSame(1, (int) $root['id']);
        $ids = array_map(static fn ($c) => (int) $c['id'], $root['children']);
        $this->assertContains(2, $ids);
        $this->assertNotContains(3, $ids);
        // 4 is active but its parent (3) is filtered out, so it never appears
        // in the tree — a flat list would still show it.
        $this->assertNotContains(4, $ids);
        $this->assertCount(1, $root['children']);
        $this->assertSame(2, $ids[0]);
    }

    public function testSyncChildrenLevelsWalksSoftDeletedChildren(): void
    {
        $repo = new BudgetCategoryRepository();
        $service = new BudgetCategoryService($repo, $this->createMock(\App\Repositories\BudgetCategoryItemRepository::class));

        // Move 3 under 2 (level 1), so 3 becomes level 2 and its child 4 becomes level 3.
        $this->assertTrue($service->update('admin', 3, new \App\Dtos\UpdateCategoryDto(parentId: 2)));

        $three = Database::queryOne('SELECT level FROM budget_categories WHERE id = 3');
        $four = Database::queryOne('SELECT level FROM budget_categories WHERE id = 4');
        $this->assertSame(2, (int) $three['level']);
        $this->assertSame(3, (int) $four['level'], 'level sync must walk through a soft-deleted child');
    }
}