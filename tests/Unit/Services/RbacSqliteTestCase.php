<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base for RBAC service tests. Uses an in-memory SQLite DB (injected via
 * Database::setInstance) seeded with a minimal roles/permissions catalogue,
 * so the tests are self-contained and run identically in CI (which does not
 * provision the MySQL test schema for unit tests).
 */
abstract class RbacSqliteTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);
        $this->createSchema();
        $this->seedRbac();
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function createSchema(): void
    {
        $this->pdo->exec(
            "CREATE TABLE organizations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT, name_th TEXT, org_type TEXT,
                parent_id INTEGER, level INTEGER DEFAULT 0, is_active INTEGER DEFAULT 1
             );
             CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                email TEXT, password TEXT, name TEXT,
                role TEXT DEFAULT 'viewer', is_active INTEGER DEFAULT 1, department TEXT
             );
             CREATE TABLE roles (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE, name_th TEXT, name_en TEXT, description TEXT,
                is_system INTEGER DEFAULT 0, is_active INTEGER DEFAULT 1, sort_order INTEGER DEFAULT 0
             );
             CREATE TABLE permissions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE, name_th TEXT, resource TEXT
             );
             CREATE TABLE role_permissions (
                role_id INTEGER, permission_id INTEGER, PRIMARY KEY(role_id, permission_id)
             );
             CREATE TABLE user_access_grants (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER, role_id INTEGER,
                scope_type TEXT DEFAULT 'organization', scope_ref_id INTEGER,
                is_active INTEGER DEFAULT 1, created_by INTEGER,
                UNIQUE(user_id, role_id, scope_type, scope_ref_id)
             );"
        );
    }

    private function seedRbac(): void
    {
        $this->pdo->exec(
            "INSERT INTO permissions (code, name_th, resource) VALUES
                ('budget.view','ดูงบ','budget'),
                ('budget.edit','แก้งบ','budget'),
                ('report.view','ดูรายงาน','report'),
                ('request.view','ดูคำขอ','request'),
                ('request.approve','อนุมัติคำขอ','request'),
                ('disbursement.view','ดูเบิกจ่าย','disbursement'),
                ('org.view','ดูหน่วยงาน','org'),
                ('user.manage','จัดการผู้ใช้','user'),
                ('role.manage','จัดการบทบาท','role'),
                ('org.manage','จัดการหน่วยงาน','org');

             INSERT INTO roles (code, name_th, is_system) VALUES
                ('super_admin','ผู้ดูแลระบบสูงสุด',1),
                ('org_admin','ผู้ดูแลหน่วยงาน',0),
                ('budget_editor','เจ้าหน้าที่งบ',0),
                ('viewer','ผู้ดู',0),
                ('executive','ผู้บริหาร',0);

             -- read set for viewer + executive
             INSERT INTO role_permissions (role_id, permission_id)
                SELECT r.id, p.id FROM roles r, permissions p
                WHERE r.code IN ('viewer','executive')
                  AND p.code IN ('budget.view','report.view','request.view','org.view','disbursement.view');
             -- budget_editor adds edit
             INSERT INTO role_permissions (role_id, permission_id)
                SELECT r.id, p.id FROM roles r, permissions p
                WHERE r.code='budget_editor'
                  AND p.code IN ('budget.view','report.view','request.view','org.view','disbursement.view','budget.edit');
             -- org_admin: read + edit + user.manage
             INSERT INTO role_permissions (role_id, permission_id)
                SELECT r.id, p.id FROM roles r, permissions p
                WHERE r.code='org_admin'
                  AND p.code IN ('budget.view','report.view','request.view','org.view','disbursement.view','budget.edit','user.manage');
             -- super_admin: all
             INSERT INTO role_permissions (role_id, permission_id)
                SELECT r.id, p.id FROM roles r, permissions p WHERE r.code='super_admin';"
        );
    }

    protected function makeOrg(?int $parentId, int $level): int
    {
        return Database::insert('organizations', [
            'code' => 'T-' . bin2hex(random_bytes(4)),
            'name_th' => 'หน่วยทดสอบ',
            'org_type' => $level === 0 ? 'department' : 'division',
            'parent_id' => $parentId,
            'level' => $level,
            'is_active' => 1,
        ]);
    }

    /** @return array{id:int,role:string} */
    protected function makeUser(string $role = 'viewer'): array
    {
        $id = Database::insert('users', [
            'email' => 'u' . bin2hex(random_bytes(4)) . '@test.local',
            'password' => 'x',
            'name' => 'Test User',
            'role' => $role,
            'is_active' => 1,
        ]);
        return ['id' => $id, 'role' => $role];
    }

    protected function makeAdmin(): array
    {
        return $this->makeUser('admin');
    }

    protected function roleId(string $code): int
    {
        return (int) Database::queryOne("SELECT id FROM roles WHERE code = ?", [$code])['id'];
    }

    protected function grant(int $userId, string $roleCode, string $scopeType, ?int $ref): void
    {
        Database::insert('user_access_grants', [
            'user_id' => $userId,
            'role_id' => $this->roleId($roleCode),
            'scope_type' => $scopeType,
            'scope_ref_id' => $ref,
            'is_active' => 1,
        ]);
    }
}
