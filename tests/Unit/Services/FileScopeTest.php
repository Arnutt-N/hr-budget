<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Services\FileService;

/**
 * Phase 10: request-attachment reads mirror the budget-request visibility from
 * Phase 9. A file attached to a request is listable/downloadable by the request
 * owner, an admin, or anyone whose granted org subtree contains the request's
 * org. Ungranted non-owners see nothing. WRITE paths (upload/delete) stay
 * owner-only and are not exercised here.
 */
class FileScopeTest extends RbacSqliteTestCase
{
    private FileService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo->exec(
            "CREATE TABLE budget_requests (
                id INTEGER PRIMARY KEY AUTOINCREMENT, created_by INTEGER, org_id INTEGER
             );
             CREATE TABLE files (
                id INTEGER PRIMARY KEY AUTOINCREMENT, folder_id INTEGER, request_id INTEGER,
                original_name TEXT, stored_name TEXT, file_path TEXT, file_type TEXT,
                file_size INTEGER, mime_type TEXT, uploaded_by INTEGER,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
             );"
        );
        $this->service = new FileService();
    }

    private function makeReq(int $orgId, int $createdBy): int
    {
        return Database::insert('budget_requests', ['created_by' => $createdBy, 'org_id' => $orgId]);
    }

    private function makeFile(int $requestId): int
    {
        return Database::insert('files', [
            'request_id' => $requestId,
            'original_name' => 'f.pdf',
            'stored_name' => 's.pdf',
            'file_path' => 'uploads/requests/' . $requestId . '/s.pdf',
            'file_type' => 'pdf',
            'file_size' => 1,
            'mime_type' => 'application/pdf',
            'uploaded_by' => 1,
        ]);
    }

    /** @return int[] */
    private function listedIds(int $requestId, int $userId, string $role): array
    {
        $files = $this->service->listByRequest($requestId, $userId, $role);
        return array_map(static fn ($r) => (int) $r['id'], $files);
    }

    /** @test */
    public function admin_sees_files_of_any_request(): void
    {
        $org = $this->makeOrg(null, 0);
        $other = $this->makeUser('viewer');
        $req = $this->makeReq($org, $other['id']);
        $f = $this->makeFile($req);

        $admin = $this->makeAdmin();
        $this->assertContains($f, $this->listedIds($req, $admin['id'], 'admin'));
    }

    /** @test */
    public function owner_sees_own_request_files(): void
    {
        $org = $this->makeOrg(null, 0);
        $owner = $this->makeUser('viewer');
        $req = $this->makeReq($org, $owner['id']);
        $f = $this->makeFile($req);

        $this->assertSame([$f], $this->listedIds($req, $owner['id'], 'viewer'));
    }

    /** @test */
    public function subtree_granted_user_sees_files_of_subtree_request(): void
    {
        $parent = $this->makeOrg(null, 0);
        $child = $this->makeOrg($parent, 1);
        $approver = $this->makeUser('viewer');
        $other = $this->makeUser('viewer');
        $this->grant($approver['id'], 'org_admin', 'organization', $parent);

        $req = $this->makeReq($child, $other['id']);   // in subtree, not owned by approver
        $f = $this->makeFile($req);

        $this->assertContains($f, $this->listedIds($req, $approver['id'], 'viewer'));
    }

    /** @test */
    public function ungranted_non_owner_sees_no_files(): void
    {
        $org = $this->makeOrg(null, 0);
        $owner = $this->makeUser('viewer');
        $stranger = $this->makeUser('viewer');
        $req = $this->makeReq($org, $owner['id']);
        $this->makeFile($req);

        $this->assertSame([], $this->listedIds($req, $stranger['id'], 'viewer'));
    }
}
