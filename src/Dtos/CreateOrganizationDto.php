<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreateOrganizationDto
{
    private const ORG_TYPES = ['ministry', 'department', 'division', 'section', 'province', 'office'];

    public function __construct(
        public readonly string $code,
        public readonly string $nameTh,
        public readonly ?string $abbreviation = null,
        public readonly ?string $orgType = null,
        public readonly ?string $region = null,
        public readonly ?int $parentId = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code === '') {
            $errors['code'] = 'กรุณาระบุรหัสหน่วยงาน';
        } elseif (mb_strlen($this->code) > 50) {
            $errors['code'] = 'รหัสหน่วยงานต้องไม่เกิน 50 ตัวอักษร';
        }

        if ($this->nameTh === '') {
            $errors['name_th'] = 'กรุณาระบุชื่อหน่วยงาน';
        } elseif (mb_strlen($this->nameTh) > 255) {
            $errors['name_th'] = 'ชื่อหน่วยงานต้องไม่เกิน 255 ตัวอักษร';
        }

        if ($this->orgType !== null && !in_array($this->orgType, self::ORG_TYPES, true)) {
            $errors['org_type'] = 'ประเภทหน่วยงานไม่ถูกต้อง';
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
            abbreviation: isset($raw['abbreviation']) ? trim((string) $raw['abbreviation']) : null,
            orgType: $raw['org_type'] ?? null,
            region: $raw['region'] ?? null,
            parentId: $parentId !== null ? (int) $parentId : null,
        );
    }
}
