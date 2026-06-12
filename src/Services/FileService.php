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
    ) {}

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
            'mime_type' => $this->detectMimeType($dto->tmpPath, $dto->extension),
            'uploaded_by' => $userId,
        ]);

        return ['success' => true, 'file' => $this->repo->findById($id)];
    }

    public function listByRequest(int $requestId, int $userId, string $role): array
    {
        $request = Database::queryOne("SELECT created_by FROM budget_requests WHERE id = ?", [$requestId]);
        if ($request === null) {
            return [];
        }

        if ($role !== 'admin' && (int) $request['created_by'] !== $userId) {
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

        if ((int) $file['request_id'] > 0) {
            $request = Database::queryOne("SELECT created_by FROM budget_requests WHERE id = ?", [(int) $file['request_id']]);
            if ($request !== null && $role !== 'admin' && (int) $request['created_by'] !== $userId) {
                return null;
            }
        }

        $osPath = str_replace('/', DIRECTORY_SEPARATOR, $file['file_path']);
        $fullPath = BASE_PATH . '/public/' . $osPath;

        if (!file_exists($fullPath)) {
            return null;
        }

        return [
            'path' => $fullPath,
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

        if ((int) $file['request_id'] > 0) {
            $request = Database::queryOne("SELECT created_by FROM budget_requests WHERE id = ?", [(int) $file['request_id']]);
            if ($request !== null && $role !== 'admin' && (int) $request['created_by'] !== $userId) {
                return false;
            }
        }

        $osPath = str_replace('/', DIRECTORY_SEPARATOR, $file['file_path']);
        $fullPath = BASE_PATH . '/public/' . $osPath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        return $this->repo->delete($id);
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
