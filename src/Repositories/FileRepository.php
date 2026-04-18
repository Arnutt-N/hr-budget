<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class FileRepository
{
    public function findByRequestId(int $requestId): array
    {
        return Database::query(
            "SELECT f.*, u.name as uploaded_by_name
             FROM files f
             LEFT JOIN users u ON f.uploaded_by = u.id
             WHERE f.request_id = ?
             ORDER BY f.created_at DESC",
            [$requestId]
        );
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM files WHERE id = ?", [$id]);
    }

    public function insert(array $data): int
    {
        return Database::insert('files', $data);
    }

    public function delete(int $id): bool
    {
        return Database::delete('files', 'id = ?', [$id]) > 0;
    }
}
