<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateDivisionDto
{
    private const VALID_TYPES = ['central', 'regional', 'provincial'];

    public function __construct(
        public readonly string $code,
        public readonly string $nameTh,
        public readonly ?string $nameEn = null,
        public readonly ?string $shortName = null,
        public readonly ?int $parentId = null,
        public readonly string $type = 'central',
        public readonly bool $isActive = true,
        public readonly int $sortOrder = 0,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code === '') {
            $errors['code'] = 'กรุณาระบุรหัสหน่วยงาน';
        } elseif (mb_strlen($this->code) > 20) {
            $errors['code'] = 'รหัสหน่วยงานต้องไม่เกิน 20 ตัวอักษร';
        }

        if ($this->nameTh === '') {
            $errors['name_th'] = 'กรุณาระบุชื่อหน่วยงาน';
        } elseif (mb_strlen($this->nameTh) > 255) {
            $errors['name_th'] = 'ชื่อหน่วยงานต้องไม่เกิน 255 ตัวอักษร';
        }

        if (!in_array($this->type, self::VALID_TYPES, true)) {
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
            code: trim((string) ($raw['code'] ?? '')),
            nameTh: trim((string) ($raw['name_th'] ?? '')),
            nameEn: isset($raw['name_en']) ? trim((string) $raw['name_en']) : null,
            shortName: isset($raw['short_name']) ? trim((string) $raw['short_name']) : null,
            parentId: isset($raw['parent_id']) && $raw['parent_id'] !== '' ? (int) $raw['parent_id'] : null,
            type: trim((string) ($raw['type'] ?? 'central')),
            isActive: (bool) ($raw['is_active'] ?? true),
            sortOrder: (int) ($raw['sort_order'] ?? 0),
        );
    }
}
