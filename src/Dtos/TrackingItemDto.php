<?php

declare(strict_types=1);

namespace App\Dtos;

/**
 * One row of monthly tracking amounts, keyed by expense_item_id.
 *
 * Amounts are kept as numeric strings ("0.00") for decimal fidelity and
 * coerced with bc* math at the service layer.
 */
final class TrackingItemDto
{
    public function __construct(
        public readonly int $expenseItemId,
        public readonly string $allocated,
        public readonly string $transfer,
        public readonly string $disbursed,
        public readonly string $pending,
        public readonly string $po,
    ) {}

    /** @return array<string,string> field → Thai error message (empty = valid) */
    public function validate(): array
    {
        $errors = [];

        if ($this->expenseItemId <= 0) {
            $errors['expense_item_id'] = 'รหัสรายการไม่ถูกต้อง';
        }

        foreach ([
            'allocated' => $this->allocated,
            'transfer' => $this->transfer,
            'disbursed' => $this->disbursed,
            'pending' => $this->pending,
            'po' => $this->po,
        ] as $field => $value) {
            if (!is_numeric($value) || bccomp($value, '0', 2) < 0) {
                $errors[$field] = 'ยอดต้องเป็นตัวเลขที่ไม่ติดลบ';
            }
        }

        return $errors;
    }

    public static function fromArray(array $raw): self
    {
        return new self(
            expenseItemId: (int) ($raw['expense_item_id'] ?? 0),
            allocated: self::coerce($raw['allocated'] ?? null),
            transfer: self::coerce($raw['transfer'] ?? null),
            disbursed: self::coerce($raw['disbursed'] ?? null),
            pending: self::coerce($raw['pending'] ?? null),
            po: self::coerce($raw['po'] ?? null),
        );
    }

    /**
     * Build the persisted amounts (numeric strings, scale 2). Caller adds
     * the expense/activity/session context columns.
     *
     * @return array<string,string>
     */
    public function amounts(): array
    {
        return [
            'allocated' => $this->normalized($this->allocated),
            'transfer' => $this->normalized($this->transfer),
            'disbursed' => $this->normalized($this->disbursed),
            'pending' => $this->normalized($this->pending),
            'po' => $this->normalized($this->po),
        ];
    }

    /** Empty/"0" → "0"; keep raw string otherwise for validation fidelity. */
    private static function coerce(mixed $value): string
    {
        if ($value === null) {
            return '0';
        }
        $str = trim((string) $value);
        return $str === '' ? '0' : $str;
    }

    private function normalized(string $value): string
    {
        return is_numeric($value) ? bcadd($value, '0', 2) : '0.00';
    }
}
