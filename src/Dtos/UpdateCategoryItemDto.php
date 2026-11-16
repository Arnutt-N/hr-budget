<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Helpers\FiscalCode;

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
        public readonly ?string $fiscalCode = null,
        public readonly ?string $expenseCatCode = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->name !== null && $this->name === '') {
            $errors['name'] = 'ชื่อรายการไม่ควรเป็นค่าว่าง';
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

        return new self(
            name: array_key_exists('name', $raw) ? trim((string) $raw['name']) : null,
            code: array_key_exists('code', $raw) ? $raw['code'] : null,
            parentId: array_key_exists('parent_id', $raw) ? ($raw['parent_id'] !== null ? (int) $raw['parent_id'] : null) : null,
            level: array_key_exists('level', $raw) ? (int) $raw['level'] : null,
            sortOrder: array_key_exists('sort_order', $raw) ? (int) $raw['sort_order'] : null,
            isActive: array_key_exists('is_active', $raw) ? (bool) $raw['is_active'] : null,
            description: array_key_exists('description', $raw) ? $raw['description'] : null,
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
