<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateOrganizationDto
{
    private const ORG_TYPES = ['ministry', 'department', 'division', 'section', 'province', 'office'];

    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $nameTh = null,
        public readonly ?string $abbreviation = null,
        public readonly ?string $orgType = null,
        public readonly ?string $region = null,
        public readonly ?int $parentId = null,
        public readonly ?bool $isActive = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->code !== null && mb_strlen($this->code) > 50) {
            $errors['code'] = 'รหัสหน่วยงานต้องไม่เกิน 50 ตัวอักษร';
        }

        if ($this->nameTh !== null && $this->nameTh === '') {
            $errors['name_th'] = 'ชื่อหน่วยงานไม่ควรเป็นค่าว่าง';
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

        return new self(
            code: array_key_exists('code', $raw) ? trim((string) $raw['code']) : null,
            nameTh: array_key_exists('name_th', $raw) ? trim((string) $raw['name_th']) : null,
            abbreviation: array_key_exists('abbreviation', $raw) ? trim((string) $raw['abbreviation']) : null,
            orgType: $raw['org_type'] ?? null,
            region: $raw['region'] ?? null,
            parentId: array_key_exists('parent_id', $raw) ? ($raw['parent_id'] !== null ? (int) $raw['parent_id'] : null) : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
        );
    }
}
