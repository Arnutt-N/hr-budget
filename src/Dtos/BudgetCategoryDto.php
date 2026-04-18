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
