<?php

declare(strict_types=1);

namespace App\Services;

use App\Dtos\NotificationQueryDto;
use App\Repositories\NotificationRepository;

final class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $repo = new NotificationRepository(),
    ) {}

    /** @return array{data: array[], meta: array} */
    public function list(int $userId, NotificationQueryDto $dto): array
    {
        $offset = ($dto->page - 1) * $dto->perPage;
        $total = $this->repo->countByUserId($userId);
        $data = $this->repo->findByUserId($userId, $dto->perPage, $offset);

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'page' => $dto->page,
                'per_page' => $dto->perPage,
                'total_pages' => $dto->perPage > 0 ? (int) ceil($total / $dto->perPage) : 0,
            ],
        ];
    }

    public function getUnreadCount(int $userId): int
    {
        return $this->repo->countUnread($userId);
    }

    public function notify(int $userId, string $type, string $title, ?string $message = null, ?string $link = null): int
    {
        return $this->repo->insert([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'is_read' => 0,
        ]);
    }

    public function markRead(int $id, int $userId): bool
    {
        $notification = $this->repo->findById($id);
        if ($notification === null || (int) $notification['user_id'] !== $userId) {
            return false;
        }

        return $this->repo->markRead($id);
    }

    public function markAllRead(int $userId): bool
    {
        return $this->repo->markAllRead($userId);
    }
}
