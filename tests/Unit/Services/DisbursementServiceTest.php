<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use App\Core\Database;
use App\Dtos\CreateDisbursementRecordDto;
use App\Dtos\CreateDisbursementSessionDto;
use App\Dtos\DisbursementSessionListQueryDto;
use App\Dtos\SaveTrackingItemsDto;
use App\Dtos\TrackingItemDto;
use App\Services\DisbursementService;

class DisbursementServiceTest extends TestCase
{
    private \PDO $pdo;

    private const ADMIN = ['id' => 1, 'role' => 'admin', 'organization_id' => 99];
    private const STAFF = ['id' => 2, 'role' => 'viewer', 'organization_id' => 3];

    protected function setUp(): void
    {
        $this->pdo = new \PDO('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        Database::setInstance($this->pdo);

        $this->pdo->exec("
            CREATE TABLE organizations (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name_th TEXT NOT NULL
            )
        ");
        $this->pdo->exec("
            CREATE TABLE plans (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name_th TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE projects (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                plan_id INTEGER,
                name_th TEXT
            )
        ");
        $this->pdo->exec("
            CREATE TABLE activities (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                project_id INTEGER,
                code TEXT,
                name_th TEXT NOT NULL,
                fiscal_year INTEGER,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT DEFAULT NULL
            )
        ");
        $this->pdo->exec("
            CREATE TABLE source_of_truth_mappings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                fiscal_year INTEGER NOT NULL,
                organization_id INTEGER NOT NULL,
                plan_id INTEGER NOT NULL,
                project_id INTEGER NOT NULL,
                activity_id INTEGER NOT NULL,
                is_official INTEGER DEFAULT 1
            )
        ");
        $this->pdo->exec("
            CREATE TABLE disbursement_sessions (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                organization_id INTEGER NOT NULL,
                fiscal_year INTEGER NOT NULL,
                record_month INTEGER NOT NULL,
                record_date TEXT NOT NULL,
                created_by INTEGER DEFAULT NULL,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (organization_id, fiscal_year, record_month)
            )
        ");
        $this->pdo->exec("
            CREATE TABLE disbursement_records (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id INTEGER NOT NULL,
                activity_id INTEGER NOT NULL,
                status TEXT DEFAULT 'draft',
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (session_id, activity_id)
            )
        ");
        $this->pdo->exec("
            CREATE TABLE budget_trackings (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                disbursement_record_id INTEGER,
                budget_type_id INTEGER,
                plan_id INTEGER,
                project_id INTEGER,
                activity_id INTEGER,
                expense_type_id INTEGER,
                expense_group_id INTEGER,
                expense_item_id INTEGER,
                fiscal_year INTEGER NOT NULL,
                record_month INTEGER,
                organization_id INTEGER,
                budget_category_item_id INTEGER,
                allocated REAL,
                transfer REAL,
                disbursed REAL,
                pending REAL,
                po REAL,
                updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (disbursement_record_id, expense_item_id)
            )
        ");
        $this->pdo->exec("
            CREATE TABLE expense_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT,
                name_th TEXT NOT NULL,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1
            )
        ");
        $this->pdo->exec("
            CREATE TABLE expense_groups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                expense_type_id INTEGER NOT NULL,
                code TEXT,
                name_th TEXT NOT NULL,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT DEFAULT NULL
            )
        ");
        $this->pdo->exec("
            CREATE TABLE expense_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                expense_group_id INTEGER,
                expense_type_id INTEGER,
                parent_id INTEGER,
                code TEXT,
                name_th TEXT NOT NULL,
                level INTEGER DEFAULT 0,
                is_header INTEGER DEFAULT 0,
                sort_order INTEGER DEFAULT 0,
                is_active INTEGER DEFAULT 1,
                deleted_at TEXT DEFAULT NULL
            )
        ");

        $this->seed();
    }

    protected function tearDown(): void
    {
        Database::resetInstance();
    }

    private function seed(): void
    {
        // Orgs: 3 = staff org (กระทรวงยุติธรรม), 99 = some admin-chosen org.
        $this->pdo->exec("INSERT INTO organizations (id, name_th) VALUES (3, 'กระทรวงยุติธรรม'), (99, 'หน่วยงานทดสอบ')");

        $this->pdo->exec("INSERT INTO plans (id, name_th) VALUES (15, 'แผนงานบุคลากรภาครัฐ')");
        $this->pdo->exec("INSERT INTO projects (id, plan_id, name_th) VALUES (21, 15, 'ผลผลิตทดสอบ')");

        // Activity 31 = official (mapped); activity 40 = fallback only.
        $this->pdo->exec("INSERT INTO activities (id, project_id, code, name_th, fiscal_year, sort_order, is_active, deleted_at)
                          VALUES (31, 21, 'AC-001', 'กิจกรรมหลัก', 2569, 1, 1, NULL),
                                 (40, 21, 'AC-040', 'กิจกรรมรอง', 2569, 2, 1, NULL)");

        // Official mapping for (fy 2569, org 3) → only activity 31.
        $this->pdo->exec("INSERT INTO source_of_truth_mappings (fiscal_year, organization_id, plan_id, project_id, activity_id, is_official)
                          VALUES (2569, 3, 15, 21, 31, 1)");

        // Expense reference tree: type 1 → group 1 → item 15.
        $this->pdo->exec("INSERT INTO expense_types (id, code, name_th, is_active) VALUES (1, 'ET-1', 'งบบุคลากร', 1)");
        $this->pdo->exec("INSERT INTO expense_groups (id, expense_type_id, code, name_th, is_active, deleted_at) VALUES (1, 1, 'EG-1', 'เงินเดือน', 1, NULL)");
        $this->pdo->exec("INSERT INTO expense_items (id, expense_group_id, expense_type_id, code, name_th, level, is_header, is_active, deleted_at)
                          VALUES (15, 1, 1, 'EI-15', 'รายการทดสอบ', 4, 0, 1, NULL),
                                 (16, 1, 1, 'EI-16', 'รายการทดสอบ 2', 4, 0, 1, NULL)");
    }

    private function service(): DisbursementService
    {
        return new DisbursementService();
    }

    // ---- session create-or-fetch idempotency ----

    /** @test */
    public function create_session_inserts_new(): void
    {
        $dto = new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01');
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, $dto);

        $this->assertNotNull($session);
        $this->assertSame(3, $session['organization_id']);
        $this->assertSame(11, $session['record_month']);
        $this->assertSame('กระทรวงยุติธรรม', $session['org_name']);
    }

    /** @test */
    public function create_session_is_idempotent(): void
    {
        $dto = new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01');
        $first = $this->service()->createOrFetchSession('admin', self::ADMIN, $dto);
        $second = $this->service()->createOrFetchSession('admin', self::ADMIN, $dto);

        $this->assertSame($first['id'], $second['id']);
        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM disbursement_sessions")->fetchColumn();
        $this->assertSame(1, $count);
    }

    /** @test */
    public function non_admin_org_is_overridden_to_own_org(): void
    {
        // Staff (own org 3) tries to create a session for org 99.
        $dto = new CreateDisbursementSessionDto(99, 2569, 11, '2026-01-01');
        $session = $this->service()->createOrFetchSession('viewer', self::STAFF, $dto);

        $this->assertNotNull($session);
        $this->assertSame(3, $session['organization_id'], 'non-admin org must be forced to own org');
    }

    /** @test */
    public function non_admin_without_org_cannot_create(): void
    {
        $dto = new CreateDisbursementSessionDto(99, 2569, 11, '2026-01-01');
        $session = $this->service()->createOrFetchSession('viewer', ['id' => 9, 'role' => 'viewer'], $dto);
        $this->assertNull($session);
    }

    /** @test */
    public function list_sessions_scopes_non_admin_to_own_org(): void
    {
        // org 3 + org 99 sessions exist.
        $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(99, 2569, 11, '2026-01-01'));

        $result = $this->service()->listSessions('viewer', self::STAFF, new DisbursementSessionListQueryDto());
        $this->assertSame(1, $result['meta']['total']);
        $this->assertSame(3, $result['data'][0]['organization_id']);
    }

    // ---- activities (source_of_truth filter + fallback + has_record) ----

    /** @test */
    public function activities_use_official_mapping_when_present(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $activities = $this->service()->getActivities('admin', self::ADMIN, (int) $session['id']);

        $this->assertNotNull($activities);
        // Only mapped activity 31 (not fallback 40).
        $this->assertCount(1, $activities);
        $this->assertSame(31, $activities[0]['activity_id']);
        $this->assertNull($activities[0]['record_id']);
        $this->assertNull($activities[0]['record_status']);
    }

    /** @test */
    public function activities_fall_back_to_all_when_no_mapping(): void
    {
        // org 99 has no official mapping → fallback to all fy-2569 active activities.
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(99, 2569, 11, '2026-01-01'));
        $activities = $this->service()->getActivities('admin', self::ADMIN, (int) $session['id']);

        $this->assertCount(2, $activities);
        $ids = array_map(static fn ($a) => $a['activity_id'], $activities);
        $this->assertContains(31, $ids);
        $this->assertContains(40, $ids);
    }

    /** @test */
    public function activities_expose_record_marker_after_record_created(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto((int) $session['id'], 31));

        $activities = $this->service()->getActivities('admin', self::ADMIN, (int) $session['id']);
        $this->assertSame(31, $activities[0]['activity_id']);
        $this->assertNotNull($activities[0]['record_id']);
        $this->assertSame('draft', $activities[0]['record_status']);
    }

    /** @test */
    public function activities_null_when_session_missing(): void
    {
        $this->assertNull($this->service()->getActivities('admin', self::ADMIN, 9999));
    }

    // ---- record create-or-fetch idempotency ----

    /** @test */
    public function create_record_is_idempotent(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $sid = (int) $session['id'];

        $r1 = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto($sid, 31));
        $r2 = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto($sid, 31));

        $this->assertSame($r1['id'], $r2['id']);
        $this->assertSame('draft', $r1['status']);
        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM disbursement_records")->fetchColumn();
        $this->assertSame(1, $count);
    }

    /** @test */
    public function create_record_null_when_session_missing(): void
    {
        $this->assertNull($this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto(9999, 31)));
    }

    // ---- save items: upsert insert→update, status, remaining ----

    /** @test */
    public function save_items_inserts_then_updates_same_row(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $record = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto((int) $session['id'], 31));
        $rid = (int) $record['id'];

        // First save → insert.
        $this->service()->saveRecordItems('admin', self::ADMIN, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '0', '30', '0', '0'),
        ]));
        $count1 = (int) $this->pdo->query("SELECT COUNT(*) FROM budget_trackings WHERE disbursement_record_id = {$rid}")->fetchColumn();
        $this->assertSame(1, $count1);

        // Second save same item → update (no duplicate).
        $detail = $this->service()->saveRecordItems('admin', self::ADMIN, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(15, '200', '0', '50', '0', '0'),
        ]));
        $count2 = (int) $this->pdo->query("SELECT COUNT(*) FROM budget_trackings WHERE disbursement_record_id = {$rid}")->fetchColumn();
        $this->assertSame(1, $count2, 'upsert must not create a duplicate row');

        $this->assertSame('200.00', $detail['trackings'][15]['allocated']);
        $this->assertSame('50.00', $detail['trackings'][15]['disbursed']);
    }

    /** @test */
    public function save_items_marks_record_completed(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $record = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto((int) $session['id'], 31));
        $rid = (int) $record['id'];

        $detail = $this->service()->saveRecordItems('admin', self::ADMIN, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '0', '0', '0', '0'),
        ]));

        $this->assertSame('completed', $detail['record']['status']);
        $status = $this->pdo->query("SELECT status FROM disbursement_records WHERE id = {$rid}")->fetchColumn();
        $this->assertSame('completed', $status);
    }

    /** @test */
    public function save_items_resolves_group_and_type_ids(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $record = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto((int) $session['id'], 31));
        $rid = (int) $record['id'];

        $this->service()->saveRecordItems('admin', self::ADMIN, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '0', '0', '0', '0'),
        ]));

        $row = $this->pdo->query("SELECT expense_group_id, expense_type_id, organization_id, fiscal_year, record_month, budget_category_item_id
                                  FROM budget_trackings WHERE disbursement_record_id = {$rid} AND expense_item_id = 15")->fetch(\PDO::FETCH_ASSOC);
        $this->assertSame(1, (int) $row['expense_group_id']);
        $this->assertSame(1, (int) $row['expense_type_id']);
        $this->assertSame(3, (int) $row['organization_id']);
        $this->assertSame(2569, (int) $row['fiscal_year']);
        $this->assertSame(11, (int) $row['record_month']);
        $this->assertNull($row['budget_category_item_id'], 'budget_category_item_id must stay NULL');
    }

    /** @test */
    public function record_detail_computes_remaining(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $record = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto((int) $session['id'], 31));
        $rid = (int) $record['id'];

        // allocated 100, transfer 10, disbursed 30, pending 5, po 5 → remaining = 70.00
        $detail = $this->service()->saveRecordItems('admin', self::ADMIN, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '10', '30', '5', '5'),
        ]));

        $this->assertSame('70.00', $detail['trackings'][15]['remaining']);
    }

    /** @test */
    public function save_items_null_when_item_unknown(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $record = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto((int) $session['id'], 31));
        $rid = (int) $record['id'];

        $detail = $this->service()->saveRecordItems('admin', self::ADMIN, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(99999, '100', '0', '0', '0', '0'),
        ]));

        $this->assertNull($detail, 'unknown expense item must abort the save');
        // Rolled back: no rows, status still draft.
        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM budget_trackings WHERE disbursement_record_id = {$rid}")->fetchColumn();
        $this->assertSame(0, $count);
        $status = $this->pdo->query("SELECT status FROM disbursement_records WHERE id = {$rid}")->fetchColumn();
        $this->assertSame('draft', $status);
    }

    // ---- delete cascade ----

    /** @test */
    public function delete_session_cascades_records_and_trackings(): void
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $sid = (int) $session['id'];
        $record = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto($sid, 31));
        $rid = (int) $record['id'];
        $this->service()->saveRecordItems('admin', self::ADMIN, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '0', '0', '0', '0'),
        ]));

        $ok = $this->service()->deleteSession('admin', self::ADMIN, $sid);
        $this->assertTrue($ok);

        $this->assertSame(0, (int) $this->pdo->query("SELECT COUNT(*) FROM disbursement_sessions WHERE id = {$sid}")->fetchColumn());
        $this->assertSame(0, (int) $this->pdo->query("SELECT COUNT(*) FROM disbursement_records WHERE session_id = {$sid}")->fetchColumn());
        $this->assertSame(0, (int) $this->pdo->query("SELECT COUNT(*) FROM budget_trackings WHERE disbursement_record_id = {$rid}")->fetchColumn());
    }

    /** @test */
    public function non_admin_cannot_delete_other_org_session(): void
    {
        // admin creates a session for org 99.
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(99, 2569, 11, '2026-01-01'));
        $sid = (int) $session['id'];

        // staff (org 3) tries to delete it.
        $ok = $this->service()->deleteSession('viewer', self::STAFF, $sid);
        $this->assertFalse($ok);
        $this->assertSame(1, (int) $this->pdo->query("SELECT COUNT(*) FROM disbursement_sessions WHERE id = {$sid}")->fetchColumn());
    }

    /** @test */
    public function delete_missing_session_returns_false(): void
    {
        $this->assertFalse($this->service()->deleteSession('admin', self::ADMIN, 9999));
    }

    // ---- expense structure tree ----

    /** @test */
    public function expense_structure_returns_nested_tree(): void
    {
        $tree = $this->service()->expenseStructure();
        $this->assertNotEmpty($tree);
        $this->assertSame(1, $tree[0]['id']);
        $this->assertSame('งบบุคลากร', $tree[0]['name_th']);
        $this->assertSame(1, $tree[0]['groups'][0]['id']);
        $items = $tree[0]['groups'][0]['items'];
        $itemIds = array_map(static fn ($i) => $i['id'], $items);
        $this->assertContains(15, $itemIds);
    }

    /** @test */
    public function expense_structure_flat_query_assembles_full_tree(): void
    {
        // Group 1 also nests item 16 (seeded); the 3-flat-query assembly must
        // keep both items under their group and the group under its type.
        $tree = $this->service()->expenseStructure();
        $items = $tree[0]['groups'][0]['items'];
        $itemIds = array_map(static fn ($i) => $i['id'], $items);
        $this->assertContains(15, $itemIds);
        $this->assertContains(16, $itemIds);
        // is_active is exposed as 0|1 int, is_header preserved.
        $this->assertSame(1, $items[0]['is_active']);
        $this->assertSame(0, $items[0]['is_header']);
    }

    // ---- BOLA / object-level ownership (CRITICAL) ----
    //
    // A session/record owned by org 3 (STAFF's own org) must be invisible to a
    // non-admin from a different org, while the owning org and admin succeed.

    private const OTHER_STAFF = ['id' => 7, 'role' => 'viewer', 'organization_id' => 77];

    /** Seed an org-3 session + record and return [sessionId, recordId]. */
    private function seedOwnedSessionAndRecord(): array
    {
        $session = $this->service()->createOrFetchSession('admin', self::ADMIN, new CreateDisbursementSessionDto(3, 2569, 11, '2026-01-01'));
        $record = $this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto((int) $session['id'], 31));
        return [(int) $session['id'], (int) $record['id']];
    }

    /** @test */
    public function get_session_denies_other_org_non_admin(): void
    {
        [$sid] = $this->seedOwnedSessionAndRecord();

        // Owner (org 3) and admin can read; foreign org 77 is denied.
        $this->assertNotNull($this->service()->getSession('viewer', self::STAFF, $sid));
        $this->assertNotNull($this->service()->getSession('admin', self::ADMIN, $sid));
        $this->assertNull(
            $this->service()->getSession('viewer', self::OTHER_STAFF, $sid),
            'non-admin from another org must not read the session'
        );
    }

    /** @test */
    public function get_activities_denies_other_org_non_admin(): void
    {
        [$sid] = $this->seedOwnedSessionAndRecord();

        $this->assertNotNull($this->service()->getActivities('viewer', self::STAFF, $sid));
        $this->assertNotNull($this->service()->getActivities('admin', self::ADMIN, $sid));
        $this->assertNull(
            $this->service()->getActivities('viewer', self::OTHER_STAFF, $sid),
            'non-admin from another org must not list activities'
        );
    }

    /** @test */
    public function create_or_fetch_record_denies_other_org_non_admin(): void
    {
        [$sid] = $this->seedOwnedSessionAndRecord();

        // Foreign org cannot create/fetch a record on org-3's session.
        $this->assertNull(
            $this->service()->createOrFetchRecord('viewer', self::OTHER_STAFF, new CreateDisbursementRecordDto($sid, 40)),
            'non-admin from another org must not create a record'
        );
        // Owner and admin can.
        $this->assertNotNull($this->service()->createOrFetchRecord('viewer', self::STAFF, new CreateDisbursementRecordDto($sid, 31)));
        $this->assertNotNull($this->service()->createOrFetchRecord('admin', self::ADMIN, new CreateDisbursementRecordDto($sid, 40)));
    }

    /** @test */
    public function get_record_detail_denies_other_org_non_admin(): void
    {
        [, $rid] = $this->seedOwnedSessionAndRecord();

        $this->assertNotNull($this->service()->getRecordDetail('viewer', self::STAFF, $rid));
        $this->assertNotNull($this->service()->getRecordDetail('admin', self::ADMIN, $rid));
        $this->assertNull(
            $this->service()->getRecordDetail('viewer', self::OTHER_STAFF, $rid),
            'non-admin from another org must not read the record detail'
        );
    }

    /** @test */
    public function save_record_items_denies_other_org_non_admin(): void
    {
        [, $rid] = $this->seedOwnedSessionAndRecord();

        $items = new SaveTrackingItemsDto([new TrackingItemDto(15, '100', '0', '0', '0', '0')]);

        // Foreign org is denied AND must not have written anything.
        $denied = $this->service()->saveRecordItems('viewer', self::OTHER_STAFF, $rid, $items);
        $this->assertNull($denied, 'non-admin from another org must not save items');
        $count = (int) $this->pdo->query("SELECT COUNT(*) FROM budget_trackings WHERE disbursement_record_id = {$rid}")->fetchColumn();
        $this->assertSame(0, $count, 'denied save must not write any tracking rows');

        // Owner succeeds.
        $this->assertNotNull($this->service()->saveRecordItems('viewer', self::STAFF, $rid, $items));
        // Admin succeeds on the same record.
        $this->assertNotNull($this->service()->saveRecordItems('admin', self::ADMIN, $rid, $items));
    }

    /** @test */
    public function non_admin_without_org_is_denied_object_access(): void
    {
        [$sid, $rid] = $this->seedOwnedSessionAndRecord();
        $noOrg = ['id' => 9, 'role' => 'viewer']; // org resolves to 0

        $this->assertNull($this->service()->getSession('viewer', $noOrg, $sid));
        $this->assertNull($this->service()->getActivities('viewer', $noOrg, $sid));
        $this->assertNull($this->service()->getRecordDetail('viewer', $noOrg, $rid));
        $this->assertNull($this->service()->createOrFetchRecord('viewer', $noOrg, new CreateDisbursementRecordDto($sid, 40)));
        $this->assertNull($this->service()->saveRecordItems('viewer', $noOrg, $rid, new SaveTrackingItemsDto([
            new TrackingItemDto(15, '100', '0', '0', '0', '0'),
        ])));
    }
}
