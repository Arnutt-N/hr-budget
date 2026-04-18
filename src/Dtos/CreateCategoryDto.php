<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateCategoryDto
{
    public function __construct(
        public readonly string $code,
        public readonly string $nameTh,
        public readonly ?string $nameEn = null,
        public readonly ?string $description = null,
        public readonly ?int $parentId = null,
        public readonly int $sortOrder = 0,
        public readonly bool $isActive = true,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code === '') {
            $errors['code'] = 'กรุณาระบุรหัสหมวด';
        } elseif (mb_strlen($this->code) > 20) {
            $errors['code'] = 'รหัสหมวดต้องไม่เกิน 20 ตัวอักษร';
        }

        if ($this->nameTh === '') {
            $errors['name_th'] = 'กรุณาระบุชื่อหมวด';
        } elseif (mb_strlen($this->nameTh) > 255) {
            $errors['name_th'] = 'ชื่อหมวดต้องไม่เกิน 255 ตัวอักษร';
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
            code: trim((string) ($raw['code'] ?? '')),
            nameTh: trim((string) ($raw['name_th'] ?? '')),
            nameEn: isset($raw['name_en']) ? trim((string) $raw['name_en']) : null,
            description: $raw['description'] ?? null,
            parentId: $parentId !== null ? (int) $parentId : null,
            sortOrder: (int) ($raw['sort_order'] ?? 0),
            isActive: (bool) ($raw['is_active'] ?? true),
        );
    }
}
