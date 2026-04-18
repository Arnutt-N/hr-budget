<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

class NotificationRepository
{
    public function findByUserId(int $userId, int $limit = 20, int $offset = 0): array
    {
        return Database::query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
    }

    public function countByUserId(int $userId): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM notifications WHERE user_id = ?", [$userId]);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function countUnread(int $userId): int
    {
        $result = Database::query("SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0", [$userId]);
        return (int) ($result[0]['total'] ?? 0);
    }

    public function findById(int $id): ?array
    {
        return Database::queryOne("SELECT * FROM notifications WHERE id = ?", [$id]);
    }

    public function insert(array $data): int
    {
        return Database::insert('notifications', $data);
    }

    public function markRead(int $id): bool
    {
        return Database::update('notifications', ['is_read' => 1], 'id = ?', [$id]) > 0;
    }

    public function markAllRead(int $userId): bool
    {
        return Database::update('notifications', ['is_read' => 1], 'user_id = ? AND is_read = 0', [$userId]) > 0;
    }
}
