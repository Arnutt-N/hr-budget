<?php

declare(strict_types=1);

namespace App\Dtos;

final class NotificationQueryDto
{
    private const MAX_PER_PAGE = 100;

    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 20,
    ) {}

    public function validate(): array
    {
        $errors = [];

        if ($this->page < 1) {
            $errors['page'] = 'หมายเลขหน้าต้องมากกว่า 0';
        }

        if ($this->perPage < 1 || $this->perPage > self::MAX_PER_PAGE) {
            $errors['per_page'] = 'จำนวนต่อหน้าต้องอยู่ระหว่าง 1-100';
        }

        return $errors;
    }

    public static function fromQueryString(): self
    {
        return new self(
            page: max(1, (int) ($_GET['page'] ?? 1)),
            perPage: min(self::MAX_PER_PAGE, max(1, (int) ($_GET['per_page'] ?? 20))),
        );
    }
}
