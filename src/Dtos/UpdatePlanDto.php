<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdatePlanDto
{
    public function __construct(
        public readonly ?string $code = null,
        public readonly ?string $nameTh = null,
        public readonly ?string $nameEn = null,
        public readonly ?string $description = null,
        public readonly ?int $fiscalYear = null,
        public readonly ?int $budgetTypeId = null,
        public readonly ?int $sortOrder = null,
        public readonly ?bool $isActive = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->nameTh !== null) {
            if ($this->nameTh === '') {
                $errors['name_th'] = 'กรุณาระบุชื่อแผนงาน/โครงการ';
            } elseif (mb_strlen($this->nameTh) > 500) {
                $errors['name_th'] = 'ชื่อต้องไม่เกิน 500 ตัวอักษร';
            }
        }

        if ($this->code !== null && mb_strlen($this->code) > 50) {
            $errors['code'] = 'รหัสต้องไม่เกิน 50 ตัวอักษร';
        }

        if ($this->fiscalYear !== null && $this->fiscalYear <= 0) {
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
            code: array_key_exists('code', $raw) ? trim((string) $raw['code']) : null,
            nameTh: array_key_exists('name_th', $raw) ? trim((string) $raw['name_th']) : null,
            nameEn: array_key_exists('name_en', $raw) ? trim((string) $raw['name_en']) : null,
            description: array_key_exists('description', $raw) ? trim((string) $raw['description']) : null,
            fiscalYear: array_key_exists('fiscal_year', $raw) ? (int) $raw['fiscal_year'] : null,
            budgetTypeId: array_key_exists('budget_type_id', $raw) ? (int) $raw['budget_type_id'] : null,
            sortOrder: array_key_exists('sort_order', $raw) ? (int) $raw['sort_order'] : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
        );
    }
}
