<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Dtos\CreateFolderDto;
use App\Services\VaultService;
use PHPUnit\Framework\TestCase;

final class VaultServiceTest extends TestCase
{
    private \PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE folders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                fiscal_year INTEGER,
                budget_category_id INTEGER,
                parent_id INTEGER,
                folder_path TEXT,
                description TEXT,
                is_system INTEGER DEFAULT 0,
                created_by INTEGER NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $this->pdo->exec("
            CREATE TABLE files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                folder_id INTEGER NOT NULL,
                original_name TEXT NOT NULL,
                stored_name TEXT NOT NULL,
                file_path TEXT NOT NULL,
                file_type TEXT NOT NULL,
                file_size INTEGER NOT NULL,
                mime_type TEXT,
                description TEXT,
                uploaded_by INTEGER NOT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");
        $this->pdo->exec("INSERT INTO users (id, name) VALUES (1, 'แอดมิน')");
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function service(): VaultService
    {
        return new VaultService();
    }

    /** @test */
    public function create_folder_as_admin_succeeds(): void
    {
        $dto = new CreateFolderDto(name: 'งบกลาง', fiscalYear: 2569);

        $result = $this->service()->createFolder($dto, 1, 'admin');

        $this->assertTrue($result['success']);
        $this->assertSame('งบกลาง', $result['folder']['name']);
        $this->assertSame(2569, (int) $result['folder']['fiscal_year']);
        $this->assertSame(0, (int) $result['folder']['is_system']);
    }

    /** @test */
    public function create_folder_as_viewer_is_denied(): void
    {
        $dto = new CreateFolderDto(name: 'x', fiscalYear: 2569);

        $result = $this->service()->createFolder($dto, 1, 'viewer');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('สิทธิ์', $result['error']);
    }

    /** @test */
    public function child_folder_inherits_parent_fiscal_year(): void
    {
        $svc = $this->service();
        $root = $svc->createFolder(new CreateFolderDto(name: 'ราก', fiscalYear: 2569), 1, 'admin');
        $rootId = (int) $root['folder']['id'];

        $child = $svc->createFolder(new CreateFolderDto(name: 'ลูก', parentId: $rootId), 1, 'editor');

        $this->assertTrue($child['success']);
        $this->assertSame(2569, (int) $child['folder']['fiscal_year']);
        $this->assertSame($rootId, (int) $child['folder']['parent_id']);
    }

    /** @test */
    public function delete_system_folder_is_blocked(): void
    {
        $this->pdo->exec(
            "INSERT INTO folders (name, fiscal_year, is_system, created_by) VALUES ('ระบบ', 2569, 1, 1)"
        );
        $id = (int) $this->pdo->lastInsertId();

        $result = $this->service()->deleteFolder($id, 'admin');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('ระบบ', $result['error']);
    }

    /** @test */
    public function delete_non_system_folder_as_editor_succeeds(): void
    {
        $svc = $this->service();
        $created = $svc->createFolder(new CreateFolderDto(name: 'ลบได้', fiscalYear: 2569), 1, 'admin');
        $id = (int) $created['folder']['id'];

        $result = $svc->deleteFolder($id, 'editor');

        $this->assertTrue($result['success']);
        $this->assertNull(Database::queryOne("SELECT id FROM folders WHERE id = ?", [$id]));
    }

    /** @test */
    public function list_folders_returns_roots_for_year(): void
    {
        $svc = $this->service();
        $svc->createFolder(new CreateFolderDto(name: 'A', fiscalYear: 2569), 1, 'admin');
        $svc->createFolder(new CreateFolderDto(name: 'B', fiscalYear: 2568), 1, 'admin');

        $roots = $svc->listFolders(2569, null);

        $this->assertCount(1, $roots);
        $this->assertSame('A', $roots[0]['name']);
    }

    /** @test */
    public function breadcrumb_returns_root_to_current_order(): void
    {
        $svc = $this->service();
        $root = $svc->createFolder(new CreateFolderDto(name: 'ราก', fiscalYear: 2569), 1, 'admin');
        $rootId = (int) $root['folder']['id'];
        $child = $svc->createFolder(new CreateFolderDto(name: 'ลูก', parentId: $rootId), 1, 'admin');
        $childId = (int) $child['folder']['id'];

        $crumb = $svc->breadcrumb($childId);

        $this->assertCount(2, $crumb);
        $this->assertSame($rootId, (int) $crumb[0]['id']);
        $this->assertSame($childId, (int) $crumb[1]['id']);
    }

    /** @test */
    public function list_files_returns_files_in_folder(): void
    {
        $this->pdo->exec(
            "INSERT INTO files (folder_id, original_name, stored_name, file_path, file_type, file_size, uploaded_by)
             VALUES (5, 'เอกสาร.pdf', 's.pdf', 'uploads/s.pdf', 'pdf', 100, 1)"
        );

        $files = $this->service()->listFiles(5);

        $this->assertCount(1, $files);
        $this->assertSame('เอกสาร.pdf', $files[0]['original_name']);
    }

    /** @test */
    public function breadcrumb_for_null_folder_is_empty(): void
    {
        $this->assertSame([], $this->service()->breadcrumb(null));
    }

    /** @test */
    public function years_returns_distinct_descending(): void
    {
        $svc = $this->service();
        $svc->createFolder(new CreateFolderDto(name: 'A', fiscalYear: 2569), 1, 'admin');
        $svc->createFolder(new CreateFolderDto(name: 'B', fiscalYear: 2568), 1, 'admin');
        $svc->createFolder(new CreateFolderDto(name: 'C', fiscalYear: 2569), 1, 'admin');

        $values = array_map(static fn ($r) => (int) $r['fiscal_year'], $svc->years());

        $this->assertSame([2569, 2568], $values);
    }

    /** @test */
    public function list_files_strips_internal_storage_columns(): void
    {
        $this->pdo->exec(
            "INSERT INTO files (folder_id, original_name, stored_name, file_path, file_type, file_size, uploaded_by)
             VALUES (5, 'x.pdf', 'secret.pdf', 'uploads/vault/5/secret.pdf', 'pdf', 100, 1)"
        );

        $files = $this->service()->listFiles(5);

        $this->assertArrayNotHasKey('file_path', $files[0]);
        $this->assertArrayNotHasKey('stored_name', $files[0]);
        $this->assertSame('x.pdf', $files[0]['original_name']);
    }

    /** @test */
    public function delete_file_as_viewer_is_denied(): void
    {
        $result = $this->service()->deleteFile(1, 'viewer');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('สิทธิ์', $result['error']);
    }

    /** @test */
    public function delete_file_as_admin_removes_db_row(): void
    {
        $this->pdo->exec(
            "INSERT INTO files (folder_id, original_name, stored_name, file_path, file_type, file_size, uploaded_by)
             VALUES (5, 'x.pdf', 's.pdf', 'uploads/missing.pdf', 'pdf', 100, 1)"
        );
        $id = (int) $this->pdo->lastInsertId();

        $result = $this->service()->deleteFile($id, 'admin');

        $this->assertTrue($result['success']);
        $this->assertNull(Database::queryOne("SELECT id FROM files WHERE id = ?", [$id]));
    }

    // ── initializeYear (fiscal-year scaffold) ────────────────────────────────

    /** Named root (level 0) + two active top-level children + one inactive. */
    private function seedCategories(): void
    {
        $this->pdo->exec("
            CREATE TABLE budget_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT,
                name_th TEXT NOT NULL,
                parent_id INTEGER,
                level INTEGER DEFAULT 0,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1
            )
        ");
        $this->pdo->exec(
            "INSERT INTO budget_categories (id, code, name_th, parent_id, level, sort_order, is_active) VALUES
             (1, 'GOVT_PERSONNEL_EXP', 'รายการค่าใช้จ่ายบุคลากรภาครัฐ', NULL, 0, 0, 1),
             (2, 'PERSONNEL', 'งบบุคลากร', 1, 1, 1, 1),
             (3, 'OPERATION', 'งบดำเนินงาน', 1, 1, 2, 1),
             (4, 'CLOSED', 'หมวดปิด', 1, 1, 3, 0)"
        );
    }

    /** @test */
    public function initialize_year_creates_one_system_folder_per_active_top_category(): void
    {
        $this->seedCategories();

        $result = $this->service()->initializeYear(2569, 1, 'admin');

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['created']); // inactive category is skipped

        $roots = $this->service()->listFolders(2569, null);
        $this->assertCount(2, $roots);
        foreach ($roots as $folder) {
            $this->assertSame(1, (int) $folder['is_system']);
            $this->assertNotNull($folder['budget_category_id']);
        }
        $names = array_map(static fn ($f) => $f['name'], $roots);
        sort($names);
        $this->assertSame(['งบดำเนินงาน', 'งบบุคลากร'], $names);
    }

    /** @test */
    public function initialize_year_is_idempotent(): void
    {
        $this->seedCategories();
        $svc = $this->service();

        $first = $svc->initializeYear(2569, 1, 'admin');
        $second = $svc->initializeYear(2569, 1, 'admin');

        $this->assertSame(2, $first['created']);
        $this->assertSame(0, $second['created']); // already scaffolded → no duplicates
        $this->assertCount(2, $svc->listFolders(2569, null));
    }

    /** @test */
    public function initialize_year_as_viewer_is_denied(): void
    {
        $this->seedCategories();

        $result = $this->service()->initializeYear(2569, 1, 'viewer');

        $this->assertFalse($result['success']);
        $this->assertSame(403, $result['status']);
        $this->assertCount(0, $this->service()->listFolders(2569, null));
    }

    /** @test */
    public function initialize_year_rejects_invalid_year(): void
    {
        $this->seedCategories();

        $result = $this->service()->initializeYear(0, 1, 'admin');

        $this->assertFalse($result['success']);
        $this->assertSame(422, $result['status']);
    }

    /** @test */
    public function initialize_year_falls_back_to_level_1_when_root_absent(): void
    {
        // No GOVT_PERSONNEL_EXP level-0 root → fall back to active level-1 rows.
        $this->pdo->exec("
            CREATE TABLE budget_categories (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT, name_th TEXT NOT NULL, parent_id INTEGER,
                level INTEGER DEFAULT 0, sort_order INTEGER DEFAULT 0, is_active INTEGER DEFAULT 1
            )
        ");
        $this->pdo->exec(
            "INSERT INTO budget_categories (name_th, level, is_active) VALUES
             ('งบกลาง', 1, 1),
             ('หมวดปิด', 1, 0)"
        );

        $result = $this->service()->initializeYear(2569, 1, 'admin');

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['created']); // only the active level-1 row
    }
}
