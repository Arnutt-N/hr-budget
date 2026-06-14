<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreatePlanDto
{
    public function __construct(
        public readonly ?string $code,
        public readonly string $nameTh,
        public readonly ?string $nameEn,
        public readonly ?string $description,
        public readonly int $fiscalYear,
        public readonly ?int $budgetTypeId = null,
        public readonly int $sortOrder = 0,
        public readonly bool $isActive = true,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->nameTh === '') {
            $errors['name_th'] = 'กรุณาระบุชื่อแผนงาน/โครงการ';
        } elseif (mb_strlen($this->nameTh) > 500) {
            $errors['name_th'] = 'ชื่อต้องไม่เกิน 500 ตัวอักษร';
        }

        if ($this->code !== null && mb_strlen($this->code) > 50) {
            $errors['code'] = 'รหัสต้องไม่เกิน 50 ตัวอักษร';
        }

        if ($this->fiscalYear <= 0) {
            $errors['fiscal_year'] = 'กรุณาเลือกปีงบประมาณ';
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
            code: array_key_exists('code', $raw) && $raw['code'] !== null
                ? trim((string) $raw['code'])
                : null,
            nameTh: trim((string) ($raw['name_th'] ?? '')),
            nameEn: array_key_exists('name_en', $raw) && $raw['name_en'] !== null
                ? trim((string) $raw['name_en'])
                : null,
            description: array_key_exists('description', $raw) && $raw['description'] !== null
                ? trim((string) $raw['description'])
                : null,
            fiscalYear: (int) ($raw['fiscal_year'] ?? 0),
            budgetTypeId: array_key_exists('budget_type_id', $raw) && $raw['budget_type_id'] !== null
                ? (int) $raw['budget_type_id']
                : null,
            sortOrder: (int) ($raw['sort_order'] ?? 0),
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : true,
        );
    }
}
