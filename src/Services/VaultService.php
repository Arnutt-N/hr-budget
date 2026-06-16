<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\CreateFileDto;
use App\Dtos\CreateFolderDto;
use App\Repositories\FileRepository;
use App\Repositories\FolderRepository;

/**
 * Document-vault orchestration: per-fiscal-year folder tree + file storage.
 *
 * Authorization (this pass): read = any authenticated user (parity with the
 * legacy web vault); mutations (create/delete folder, upload/delete file) =
 * role admin|editor. System folders (is_system=1) cannot be deleted.
 *
 * Org-scoping is intentionally deferred: folders/files carry an organization_id
 * column but visibility is currently open to all authenticated users (legacy
 * parity). Tightening to per-org visibility is a tracked follow-up.
 *
 * Files are stored under public/uploads/vault/<folderId>/ — the path is derived
 * from the integer folder id only, so no user-controlled string ever reaches
 * the filesystem (traversal-proof). Direct web access to that tree is blocked
 * by public/uploads/.htaccess; files are served only via the authenticated
 * download endpoint. Internal storage columns (file_path/stored_name/folder_path)
 * are stripped from API responses.
 */
final class VaultService
{
    private const UPLOAD_BASE = 'uploads/vault';
    private const ROLES_MUTATE = ['admin', 'editor'];

    public function __construct(
        private readonly FolderRepository $folders = new FolderRepository(),
        private readonly FileRepository $files = new FileRepository(),
    ) {}

    public function listFolders(int $fiscalYear, ?int $parentId): array
    {
        $rows = $parentId !== null
            ? $this->folders->findChildren($parentId)
            : $this->folders->findRoots($fiscalYear);

        return array_map([$this, 'publicFolder'], $rows);
    }

    public function tree(int $fiscalYear): array
    {
        return $this->publicTree($this->folders->findTree($fiscalYear));
    }

    public function years(): array
    {
        return $this->folders->availableYears();
    }

    /**
     * Scaffold a fiscal year's vault with one system (is_system=1) root folder
     * per top-level budget category. Idempotent: categories already scaffolded
     * for the year are skipped, so re-running only fills gaps. Ports the legacy
     * App\Models\Folder::initializeForYear() bootstrap (the `/files/init` route).
     *
     * @return array{success: bool, created?: int, error?: string, status?: int}
     */
    public function initializeYear(int $fiscalYear, int $userId, string $role): array
    {
        if (!$this->canMutate($role)) {
            return ['success' => false, 'error' => 'ไม่มีสิทธิ์ดำเนินการ', 'status' => 403];
        }
        if ($fiscalYear <= 0) {
            return ['success' => false, 'error' => 'ปีงบประมาณไม่ถูกต้อง', 'status' => 422];
        }

        $created = 0;
        foreach ($this->folders->topLevelCategories() as $cat) {
            $categoryId = (int) $cat['id'];
            if ($this->folders->findRootByCategory($fiscalYear, $categoryId) !== null) {
                continue;
            }

            $this->folders->create([
                'name' => $cat['name_th'],
                'fiscal_year' => $fiscalYear,
                'budget_category_id' => $categoryId,
                'is_system' => 1,
                'created_by' => $userId,
            ]);
            $created++;
        }

        return ['success' => true, 'created' => $created];
    }

    public function breadcrumb(?int $folderId): array
    {
        return $folderId !== null ? $this->folders->breadcrumb($folderId) : [];
    }

    public function listFiles(int $folderId): array
    {
        return array_map([$this, 'publicFile'], $this->files->findByFolderId($folderId));
    }

    /** @return array{success: bool, folder?: array|null, error?: string, status?: int} */
    public function createFolder(CreateFolderDto $dto, int $userId, string $role): array
    {
        if (!$this->canMutate($role)) {
            return ['success' => false, 'error' => 'ไม่มีสิทธิ์ดำเนินการ', 'status' => 403];
        }

        $fiscalYear = $dto->fiscalYear;
        if ($dto->parentId !== null) {
            $parent = $this->folders->findById($dto->parentId);
            if ($parent === null) {
                return ['success' => false, 'error' => 'ไม่พบโฟลเดอร์แม่', 'status' => 404];
            }
            $fiscalYear = $parent['fiscal_year'] !== null ? (int) $parent['fiscal_year'] : null;
        }

        $id = $this->folders->create([
            'name' => $dto->name,
            'parent_id' => $dto->parentId,
            'fiscal_year' => $fiscalYear,
            'description' => $dto->description,
            'is_system' => 0,
            'created_by' => $userId,
        ]);

        $folder = $this->folders->findById($id);

        return ['success' => true, 'folder' => $folder !== null ? $this->publicFolder($folder) : null];
    }

    /** @return array{success: bool, error?: string, status?: int} */
    public function deleteFolder(int $id, string $role): array
    {
        if (!$this->canMutate($role)) {
            return ['success' => false, 'error' => 'ไม่มีสิทธิ์ดำเนินการ', 'status' => 403];
        }

        $folder = $this->folders->findById($id);
        if ($folder === null) {
            return ['success' => false, 'error' => 'ไม่พบโฟลเดอร์', 'status' => 404];
        }
        if (!empty($folder['is_system'])) {
            return ['success' => false, 'error' => 'ไม่สามารถลบโฟลเดอร์ระบบได้', 'status' => 422];
        }

        // Unlink physical files in this folder + all descendants BEFORE the DB
        // cascade removes their rows (otherwise they orphan on disk).
        foreach ($this->collectFolderIds($id) as $fid) {
            foreach ($this->files->findByFolderId($fid) as $file) {
                $full = $this->containedPath((string) $file['file_path']);
                if ($full !== null && is_file($full)) {
                    unlink($full);
                }
            }
        }

        $this->folders->delete($id);

        return ['success' => true];
    }

    /** @return array{success: bool, file?: array|null, error?: string, status?: int} */
    public function upload(int $folderId, CreateFileDto $dto, int $userId, string $role): array
    {
        if (!$this->canMutate($role)) {
            return ['success' => false, 'error' => 'ไม่มีสิทธิ์ดำเนินการ', 'status' => 403];
        }
        if ($dto->tmpPath === null || $dto->extension === null) {
            return ['success' => false, 'error' => 'ไฟล์ไม่ถูกต้อง', 'status' => 422];
        }

        $folder = $this->folders->findById($folderId);
        if ($folder === null) {
            return ['success' => false, 'error' => 'ไม่พบโฟลเดอร์', 'status' => 404];
        }

        // Storage path derived from the integer folder id only (traversal-proof).
        $relativePath = self::UPLOAD_BASE . '/' . $folderId;
        $fullDir = BASE_PATH . '/public/' . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        if (!is_dir($fullDir) && !mkdir($fullDir, 0755, true)) {
            return ['success' => false, 'error' => 'สร้างโฟลเดอร์จัดเก็บไม่สำเร็จ', 'status' => 500];
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $dto->extension;
        $destination = $fullDir . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file($dto->tmpPath, $destination)) {
            return ['success' => false, 'error' => 'บันทึกไฟล์ไม่สำเร็จ', 'status' => 500];
        }

        $id = $this->files->insert([
            'folder_id' => $folderId,
            'original_name' => $dto->originalName,
            'stored_name' => $storedName,
            'file_path' => $relativePath . '/' . $storedName,
            'file_type' => $dto->extension,
            'file_size' => $dto->size,
            // Detect from the MOVED file (the tmp file no longer exists post-move).
            'mime_type' => $this->detectMimeType($destination, $dto->extension),
            'description' => $dto->description ?? null,
            'uploaded_by' => $userId,
        ]);

        $file = $this->files->findById($id);

        return ['success' => true, 'file' => $file !== null ? $this->publicFile($file) : null];
    }

    /** @return array{success: bool, error?: string, status?: int} */
    public function deleteFile(int $id, string $role): array
    {
        if (!$this->canMutate($role)) {
            return ['success' => false, 'error' => 'ไม่มีสิทธิ์ดำเนินการ', 'status' => 403];
        }

        $file = $this->files->findById($id);
        if ($file === null) {
            return ['success' => false, 'error' => 'ไม่พบไฟล์', 'status' => 404];
        }

        $full = $this->containedPath((string) $file['file_path']);
        if ($full !== null && is_file($full)) {
            unlink($full);
        }
        $this->files->delete($id);

        return ['success' => true];
    }

    /** @return array{path: string, name: string, mime: string}|null */
    public function getDownloadInfo(int $id): ?array
    {
        $file = $this->files->findById($id);
        if ($file === null) {
            return null;
        }

        $full = $this->containedPath((string) $file['file_path']);
        if ($full === null || !is_file($full)) {
            return null;
        }

        return [
            'path' => $full,
            'name' => (string) $file['original_name'],
            'mime' => (string) ($file['mime_type'] ?? 'application/octet-stream'),
        ];
    }

    private function canMutate(string $role): bool
    {
        return in_array($role, self::ROLES_MUTATE, true);
    }

    /** BFS the folder id + all descendant folder ids. */
    private function collectFolderIds(int $rootId): array
    {
        $ids = [$rootId];
        $queue = [$rootId];
        while ($queue !== []) {
            $current = (int) array_shift($queue);
            foreach ($this->folders->findChildren($current) as $child) {
                $childId = (int) $child['id'];
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    /** Strip internal storage column before returning a folder to the client. */
    private function publicFolder(array $folder): array
    {
        unset($folder['folder_path']);

        return $folder;
    }

    /** Strip internal storage columns before returning a file to the client. */
    private function publicFile(array $file): array
    {
        unset($file['file_path'], $file['stored_name']);

        return $file;
    }

    private function publicTree(array $nodes): array
    {
        return array_map(function (array $node): array {
            $node = $this->publicFolder($node);
            if (isset($node['children']) && is_array($node['children'])) {
                $node['children'] = $this->publicTree($node['children']);
            }

            return $node;
        }, $nodes);
    }

    /** Resolve a stored web path and ensure it stays under public/. */
    private function containedPath(string $webPath): ?string
    {
        $osPath = str_replace('/', DIRECTORY_SEPARATOR, $webPath);
        $real = realpath(BASE_PATH . '/public/' . $osPath);
        $root = realpath(BASE_PATH . '/public');

        if ($real === false || $root === false || !str_starts_with($real, $root . DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $real;
    }

    private function detectMimeType(string $path, string $extension): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo !== false ? finfo_file($finfo, $path) : false;
        if ($finfo !== false) {
            finfo_close($finfo);
        }

        if ($detected !== false && $detected !== 'application/octet-stream') {
            return $detected;
        }

        $mimeMap = [
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'csv' => 'text/csv',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
        ];

        return $mimeMap[$extension] ?? 'application/octet-stream';
    }
}
