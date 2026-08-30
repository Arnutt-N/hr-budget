<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use App\Dtos\CreateFileDto;
use App\Repositories\FileRepository;

final class FileService
{
    private const UPLOAD_BASE = 'uploads/requests';

    public function __construct(
        private readonly FileRepository $repo = new FileRepository(),
        private readonly AccessScopeResolver $scopeResolver = new AccessScopeResolver(),
    ) {}

    /**
     * Additive READ visibility for a request's attachments, mirroring the
     * budget-request scoping from Phase 9 (BudgetRequestService::canViewRequest):
     * admin, the request owner, an org-wide 'all' grant, or a viewer whose
     * granted org subtree contains the request's org. Keeps file listing/download
     * consistent with which requests the viewer can already see.
     *
     * @param array<string,mixed> $request a budget_requests row with created_by + org_id
     */
    private function canReadRequest(int $userId, string $role, array $request): bool
    {
        if ($role === 'admin') {
            return true;
        }
        if ((int) $request['created_by'] === $userId) {
            return true;
        }
        $scope = $this->scopeResolver->resolve(['id' => $userId, 'role' => $role]);
        if ($scope['hasAll']) {
            return true;
        }
        $orgId = isset($request['org_id']) && $request['org_id'] !== null ? (int) $request['org_id'] : null;
        return $orgId !== null && in_array($orgId, $scope['orgIds'], true);
    }

    /** @return array{success: bool, file?: array, error?: string} */
    public function upload(int $requestId, CreateFileDto $dto, int $userId, string $role): array
    {
        $request = Database::queryOne("SELECT * FROM budget_requests WHERE id = ?", [$requestId]);
        if ($request === null) {
            return ['success' => false, 'error' => 'ไม่พบคำขอ'];
        }

        if ($role !== 'admin' && (int) $request['created_by'] !== $userId) {
            return ['success' => false, 'error' => 'ไม่มีสิทธิ์แนบไฟล์'];
        }

        $relativePath = self::UPLOAD_BASE . '/' . $requestId;
        $fullPath = BASE_PATH . '/public/' . $relativePath;

        if (!is_dir($fullPath)) {
            if (!mkdir($fullPath, 0755, true)) {
                return ['success' => false, 'error' => 'สร้างโฟลเดอร์ไม่สำเร็จ'];
            }
        }

        $storedName = uniqid() . '_' . time() . '.' . $dto->extension;
        $destination = $fullPath . '/' . $storedName;

        // Detect the MIME type before move_uploaded_file() — the tmp file is
        // gone once moved, and finfo_file() on the stale path throws.
        $mimeType = $this->detectMimeType($dto->tmpPath, $dto->extension);

        if (!move_uploaded_file($dto->tmpPath, $destination)) {
            return ['success' => false, 'error' => 'บันทึกไฟล์ไม่สำเร็จ'];
        }

        $id = $this->repo->insert([
            'folder_id' => null,
            'request_id' => $requestId,
            'original_name' => $dto->originalName,
            'stored_name' => $storedName,
            'file_path' => $relativePath . '/' . $storedName,
            'file_type' => $dto->extension,
            'file_size' => $dto->size,
            'mime_type' => $mimeType,
            'uploaded_by' => $userId,
        ]);

        return ['success' => true, 'file' => $this->repo->findById($id)];
    }

    public function listByRequest(int $requestId, int $userId, string $role): array
    {
        $request = Database::queryOne("SELECT created_by, org_id FROM budget_requests WHERE id = ?", [$requestId]);
        if ($request === null) {
            return [];
        }

        if (!$this->canReadRequest($userId, $role, $request)) {
            return [];
        }

        return $this->repo->findByRequestId($requestId);
    }

    /** @return array{path: string, name: string, mime: string}|null */
    public function getDownloadInfo(int $id, int $userId, string $role): ?array
    {
        $file = $this->repo->findById($id);
        if ($file === null) {
            return null;
        }

        $requestId = (int) $file['request_id'];
        if ($requestId > 0) {
            $request = Database::queryOne("SELECT created_by, org_id FROM budget_requests WHERE id = ?", [$requestId]);
            if ($request === null) {
                // Stale attachment whose parent request is gone: only admins may read it.
                if ($role !== 'admin') {
                    return null;
                }
            } elseif (!$this->canReadRequest($userId, $role, $request)) {
                return null;
            }
        }
        // request_id NULL/0 = vault file → readable by any authenticated user
        // (VaultService read policy), so no extra check falls through here.

        $full = $this->containedPath((string) $file['file_path']);
        if ($full === null || !is_file($full)) {
            return null;
        }

        return [
            'path' => $full,
            'name' => $file['original_name'],
            'mime' => $file['mime_type'],
        ];
    }

    public function delete(int $id, int $userId, string $role): bool
    {
        $file = $this->repo->findById($id);
        if ($file === null) {
            return false;
        }

        $requestId = (int) $file['request_id'];
        if ($requestId > 0) {
            $request = Database::queryOne("SELECT created_by FROM budget_requests WHERE id = ?", [$requestId]);
            if ($request === null) {
                // Stale attachment: parent request gone → admin only.
                if ($role !== 'admin') {
                    return false;
                }
            } elseif ($role !== 'admin' && (int) $request['created_by'] !== $userId) {
                return false;
            }
        } elseif (!in_array($role, ['admin', 'editor'], true)) {
            // Vault file (request_id NULL/0): the request-attachment ownership
            // check cannot apply, so fall back to the vault mutate policy
            // (admin|editor) — otherwise any viewer could delete vault blobs
            // through this endpoint, bypassing VaultService's gate.
            return false;
        }

        $full = $this->containedPath((string) $file['file_path']);
        if ($full !== null && is_file($full)) {
            unlink($full);
        }

        return $this->repo->delete($id);
    }

    /**
     * Resolve a stored web path and ensure it stays under public/ — mirrors
     * VaultService::containedPath() so files.file_path can never turn this
     * service's delete/download into an arbitrary-path primitive.
     */
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

    private function detectMimeType(string $tmpPath, string $extension): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected = $finfo !== false ? finfo_file($finfo, $tmpPath) : false;
        finfo_close($finfo);

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
