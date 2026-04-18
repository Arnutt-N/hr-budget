<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateCategoryItemDto
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $code = null,
        public readonly ?int $parentId = null,
        public readonly ?int $level = null,
        public readonly ?int $sortOrder = null,
        public readonly ?bool $isActive = null,
        public readonly ?string $description = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->name !== null && $this->name === '') {
            $errors['name'] = 'ชื่อรายการไม่ควรเป็นค่าว่าง';
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

        return new self(
            name: array_key_exists('name', $raw) ? trim((string) $raw['name']) : null,
            code: array_key_exists('code', $raw) ? $raw['code'] : null,
            parentId: array_key_exists('parent_id', $raw) ? ($raw['parent_id'] !== null ? (int) $raw['parent_id'] : null) : null,
            level: array_key_exists('level', $raw) ? (int) $raw['level'] : null,
            sortOrder: array_key_exists('sort_order', $raw) ? (int) $raw['sort_order'] : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
            description: array_key_exists('description', $raw) ? $raw['description'] : null,
        );
    }
}
