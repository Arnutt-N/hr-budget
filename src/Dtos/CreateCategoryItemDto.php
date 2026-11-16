<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Helpers\FiscalCode;

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
        public readonly ?string $fiscalCode = null,
        public readonly ?string $expenseCatCode = null,
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

        // ponytail: validate-only; PR3 adds no fiscal columns to budget_category_items.
        // Upgrade when that table gains fiscal_code/expense_cat_code columns.
        if ($this->fiscalCode !== null) {
            if (!FiscalCode::isValid20($this->fiscalCode)) {
                $errors['fiscal_code'] = 'รหัสงบประมาณต้องมี 20 หลัก';
            } elseif (!FiscalCode::isValidExpenseCategory($this->fiscalCode)) {
                $errors['fiscal_code'] = 'หมวดหมู่รหัสงบประมาณไม่ถูกต้อง';
            }
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
            // No trim: FiscalCode uses \z so a trailing newline stays invalid.
            fiscalCode: array_key_exists('fiscal_code', $raw) && $raw['fiscal_code'] !== null
                ? (string) $raw['fiscal_code']
                : null,
            expenseCatCode: array_key_exists('expense_cat_code', $raw) && $raw['expense_cat_code'] !== null
                ? (string) $raw['expense_cat_code']
                : null,
        );
    }
}
