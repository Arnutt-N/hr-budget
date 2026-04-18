<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateCategoryDto
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $nameTh = null,
        public readonly ?string $nameEn = null,
        public readonly ?string $description = null,
        public readonly ?int $parentId = null,
        public readonly ?int $sortOrder = null,
        public readonly ?bool $isActive = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code !== null && mb_strlen($this->code) > 20) {
            $errors['code'] = 'รหัสหมวดต้องไม่เกิน 20 ตัวอักษร';
        }

        if ($this->nameTh !== null && $this->nameTh === '') {
            $errors['name_th'] = 'ชื่อหมวดไม่ควรเป็นค่าว่าง';
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
            code: array_key_exists('code', $raw) ? trim((string) $raw['code']) : null,
            nameTh: array_key_exists('name_th', $raw) ? trim((string) $raw['name_th']) : null,
            nameEn: array_key_exists('name_en', $raw) ? trim((string) $raw['name_en']) : null,
            description: array_key_exists('description', $raw) ? $raw['description'] : null,
            parentId: array_key_exists('parent_id', $raw) ? ($raw['parent_id'] !== null ? (int) $raw['parent_id'] : null) : null,
            sortOrder: array_key_exists('sort_order', $raw) ? (int) $raw['sort_order'] : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
        );
    }
}
