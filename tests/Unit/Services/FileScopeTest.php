<?php
declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Core\Database;
use App\Services\FileService;

/**
 * Phase 10: request-attachment reads mirror the budget-request visibility from
 * Phase 9. A file attached to a request is listable/downloadable by the request
 * owner, an admin, or anyone whose granted org subtree contains the request's
 * org. Ungranted non-owners see nothing. WRITE paths are owner/admin-only for
 * request attachments; vault files (request_id NULL/0) follow the VaultService
 * policies — read = any authenticated user, mutate (delete) = admin|editor.
 */
class FileScopeTest extends RbacSqliteTestCase
{
    private FileService $service;

    /** @var list<string> disk files created by tests, removed in tearDown */
    private array $tempPaths = [];

    /** @var list<string> directories created for those files, rmdir'd in tearDown */
    private array $tempDirs = [];

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

    protected function tearDown(): void
    {
        foreach ($this->tempPaths as $path) {
            @unlink($path);
        }
        $this->tempPaths = [];
        // Remove the (now-empty) directories the files lived in, stopping at
        // the uploads root — never touch anything outside uploads/test-tmp.
        foreach ($this->tempDirs as $dir) {
            for ($d = $dir; str_contains($d, 'test-tmp'); $d = dirname($d)) {
                if (!@rmdir($d)) {
                    break;
                }
            }
        }
        $this->tempDirs = [];
        parent::tearDown();
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
            // test-tmp only — these paths must never overlap real uploads
            'file_path' => 'uploads/test-tmp/req-' . $requestId . '/s.pdf',
            'file_type' => 'pdf',
            'file_size' => 1,
            'mime_type' => 'application/pdf',
            'uploaded_by' => 1,
        ]);
    }

    private function makeVaultFile(): int
    {
        return Database::insert('files', [
            'request_id' => null,
            'folder_id' => 1,
            'original_name' => 'vault.pdf',
            'stored_name' => 'vault.pdf',
            'file_path' => 'uploads/test-tmp/vault/vault.pdf',
            'file_type' => 'pdf',
            'file_size' => 1,
            'mime_type' => 'application/pdf',
            'uploaded_by' => 1,
        ]);
    }

    /** Create a real file on disk so download-info allow-paths reach file_exists(). */
    private function makeDiskFile(string $relativePath): string
    {
        $full = BASE_PATH . '/public/' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        $dir = dirname($full);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($full, 'pdf-bytes');
        $this->tempPaths[] = $full;
        $this->tempDirs[] = $dir;

        return $full;
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

    // ---- delete scoping (request attachments) ----

    /** @test */
    public function owner_can_delete_own_request_attachment(): void
    {
        $org = $this->makeOrg(null, 0);
        $owner = $this->makeUser('viewer');
        $req = $this->makeReq($org, $owner['id']);
        $f = $this->makeFile($req);

        $this->assertTrue($this->service->delete($f, $owner['id'], 'viewer'));
    }

    /** @test */
    public function non_owner_cannot_delete_request_attachment(): void
    {
        $org = $this->makeOrg(null, 0);
        $owner = $this->makeUser('viewer');
        $stranger = $this->makeUser('viewer');
        $req = $this->makeReq($org, $owner['id']);
        $f = $this->makeFile($req);

        $this->assertFalse($this->service->delete($f, $stranger['id'], 'viewer'));
    }

    /** @test */
    public function stale_attachment_denies_delete_for_non_admin_but_allows_admin(): void
    {
        // request_id points at a budget_requests row that does not exist.
        $f = Database::insert('files', [
            'request_id' => 99999,
            'original_name' => 'stale.pdf',
            'stored_name' => 'stale.pdf',
            'file_path' => 'uploads/requests/99999/stale.pdf',
            'file_type' => 'pdf',
            'file_size' => 1,
            'mime_type' => 'application/pdf',
            'uploaded_by' => 1,
        ]);
        $viewer = $this->makeUser('viewer');
        $admin = $this->makeAdmin();

        $this->assertFalse($this->service->delete($f, $viewer['id'], 'viewer'));
        $this->assertTrue($this->service->delete($f, $admin['id'], 'admin'));
    }

    // ---- delete scoping (vault files: request_id NULL) ----

    /** @test */
    public function viewer_cannot_delete_vault_file(): void
    {
        $f = $this->makeVaultFile();
        $viewer = $this->makeUser('viewer');

        $this->assertFalse($this->service->delete($f, $viewer['id'], 'viewer'));
    }

    /** @test */
    public function editor_can_delete_vault_file(): void
    {
        $f = $this->makeVaultFile();
        $editor = $this->makeUser('editor');

        $this->assertTrue($this->service->delete($f, $editor['id'], 'editor'));
    }

    /** @test */
    public function admin_can_delete_vault_file(): void
    {
        $f = $this->makeVaultFile();
        $admin = $this->makeAdmin();

        $this->assertTrue($this->service->delete($f, $admin['id'], 'admin'));
    }

    // ---- download-info scoping ----

    /** @test */
    public function viewer_cannot_download_stale_attachment(): void
    {
        Database::insert('files', [
            'request_id' => 99999,
            'original_name' => 'stale.pdf',
            'stored_name' => 'stale.pdf',
            'file_path' => 'uploads/requests/99999/stale.pdf',
            'file_type' => 'pdf',
            'file_size' => 1,
            'mime_type' => 'application/pdf',
            'uploaded_by' => 1,
        ]);
        $viewer = $this->makeUser('viewer');
        $id = (int) Database::queryOne("SELECT id FROM files WHERE request_id = 99999")['id'];

        $this->assertNull($this->service->getDownloadInfo($id, $viewer['id'], 'viewer'));
    }

    /** @test */
    public function viewer_can_download_vault_file_but_not_others_request_attachment(): void
    {
        $org = $this->makeOrg(null, 0);
        $owner = $this->makeUser('viewer');
        $stranger = $this->makeUser('viewer');
        $req = $this->makeReq($org, $owner['id']);
        $this->makeDiskFile('uploads/test-tmp/req-' . $req . '/s.pdf');
        $attached = $this->makeFile($req);
        $this->makeDiskFile('uploads/test-tmp/vault/vault.pdf');
        $vault = $this->makeVaultFile();

        $vaultInfo = $this->service->getDownloadInfo($vault, $stranger['id'], 'viewer');
        $this->assertNotNull($vaultInfo);
        $this->assertSame('vault.pdf', $vaultInfo['name']);
        $this->assertNull($this->service->getDownloadInfo($attached, $stranger['id'], 'viewer'));
    }
}
