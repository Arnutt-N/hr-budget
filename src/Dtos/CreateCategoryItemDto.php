<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateCategoryItemDto
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $code = null,
        public readonly ?int $parentId = null,
        public readonly int $level = 0,
        public readonly int $sortOrder = 0,
        public readonly bool $isActive = true,
        public readonly ?string $description = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->name === '') {
            $errors['name'] = 'กรุณาระบุชื่อรายการ';
        } elseif (mb_strlen($this->name) > 255) {
            $errors['name'] = 'ชื่อรายการต้องไม่เกิน 255 ตัวอักษร';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = [];
        $body = file_get_contents('php://input');
        if ($body !== false && $body !== '') {
            $decoded = json_decode($body, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        $parentId = $raw['parent_id'] ?? null;

        return new self(
            name: trim((string) ($raw['name'] ?? '')),
            code: $raw['code'] ?? null,
            parentId: $parentId !== null ? (int) $parentId : null,
            level: (int) ($raw['level'] ?? 0),
            sortOrder: (int) ($raw['sort_order'] ?? 0),
            isActive: (bool) ($raw['is_active'] ?? true),
            description: $raw['description'] ?? null,
        );
    }
}
