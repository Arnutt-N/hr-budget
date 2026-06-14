<?php

declare(strict_types=1);

namespace App\Dtos;

final class UpdateBudgetTargetDto
{
    public function __construct(
        public readonly ?int $targetTypeId = null,
        public readonly ?int $fiscalYear = null,
        public readonly ?int $quarter = null,
        public readonly ?int $organizationId = null,
        public readonly ?int $categoryId = null,
        public readonly ?float $targetPercent = null,
        public readonly ?float $targetAmount = null,
        public readonly ?string $notes = null,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->targetTypeId !== null && $this->targetTypeId <= 0) {
            $errors['target_type_id'] = 'กรุณาเลือกประเภทเป้าหมาย';
        }

        if ($this->fiscalYear !== null && $this->fiscalYear <= 0) {
            $errors['fiscal_year'] = 'กรุณาเลือกปีงบประมาณ';
        }

        if ($this->quarter !== null && ($this->quarter < 1 || $this->quarter > 4)) {
            $errors['quarter'] = 'ไตรมาสต้องอยู่ระหว่าง 1-4';
        }

        if ($this->targetPercent !== null && ($this->targetPercent < 0 || $this->targetPercent > 100)) {
            $errors['target_percent'] = 'เปอร์เซ็นต์ต้องอยู่ระหว่าง 0-100';
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

        // For required scalars (target_type_id, fiscal_year) a present-but-empty
        // value casts to 0 and is caught by validate(). For the nullable columns
        // (quarter/org/category/percent/amount) an empty/null value must stay NULL
        // — never 0 — or it corrupts the FK / the uk_budget_targets unique index.
        return new self(
            targetTypeId: array_key_exists('target_type_id', $raw) ? (int) $raw['target_type_id'] : null,
            fiscalYear: array_key_exists('fiscal_year', $raw) ? (int) $raw['fiscal_year'] : null,
            quarter: self::nullableInt($raw, 'quarter'),
            organizationId: self::nullableInt($raw, 'organization_id'),
            categoryId: self::nullableInt($raw, 'category_id'),
            targetPercent: self::nullableFloat($raw, 'target_percent'),
            targetAmount: self::nullableFloat($raw, 'target_amount'),
            notes: array_key_exists('notes', $raw) ? trim((string) $raw['notes']) : null,
        );
    }

    private static function nullableInt(array $raw, string $key): ?int
    {
        if (!array_key_exists($key, $raw)) {
            return null;
        }
        $value = $raw[$key];
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value;
    }

    private static function nullableFloat(array $raw, string $key): ?float
    {
        if (!array_key_exists($key, $raw)) {
            return null;
        }
        $value = $raw[$key];
        if ($value === null || $value === '') {
            return null;
        }
        return (float) $value;
    }
}
