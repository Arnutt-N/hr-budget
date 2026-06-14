<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateDivisionDto
{
    private const VALID_TYPES = ['central', 'regional', 'provincial'];

    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $nameTh = null,
        public readonly ?string $nameEn = null,
        public readonly ?string $shortName = null,
        public readonly ?int $parentId = null,
        public readonly ?string $type = null,
        public readonly ?bool $isActive = null,
        public readonly ?int $sortOrder = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code !== null) {
            if ($this->code === '') {
                $errors['code'] = 'กรุณาระบุรหัสหน่วยงาน';
            } elseif (mb_strlen($this->code) > 20) {
                $errors['code'] = 'รหัสหน่วยงานต้องไม่เกิน 20 ตัวอักษร';
            }
        }

        if ($this->nameTh !== null) {
            if ($this->nameTh === '') {
                $errors['name_th'] = 'กรุณาระบุชื่อหน่วยงาน';
            } elseif (mb_strlen($this->nameTh) > 255) {
                $errors['name_th'] = 'ชื่อหน่วยงานต้องไม่เกิน 255 ตัวอักษร';
            }
        }

        if ($this->type !== null && !in_array($this->type, self::VALID_TYPES, true)) {
            $errors['type'] = 'ประเภทไม่ถูกต้อง';
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
            shortName: array_key_exists('short_name', $raw) ? trim((string) $raw['short_name']) : null,
            parentId: array_key_exists('parent_id', $raw)
                ? ($raw['parent_id'] === '' || $raw['parent_id'] === null ? null : (int) $raw['parent_id'])
                : null,
            type: array_key_exists('type', $raw) ? trim((string) $raw['type']) : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
            sortOrder: array_key_exists('sort_order', $raw) ? (int) $raw['sort_order'] : null,
        );
    }
}
