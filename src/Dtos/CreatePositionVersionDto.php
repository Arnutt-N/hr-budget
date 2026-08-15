<?php

declare(strict_types=1);

namespace App\Dtos;

final class CreatePositionVersionDto
{
    public function __construct(
        public readonly int $organizationId,
        public readonly ?string $posNo,
        public readonly ?string $levelCode,
        public readonly ?string $lineCode,
        public readonly float $baseSalary,
        public readonly string $salaryBasis,
        public readonly ?float $salaryPreRaise,
        public readonly string $occupancy,
        public readonly string $lifecycle,
        public readonly int $monthsCounted,
        public readonly string $approvalStatus,
        public readonly string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $orderDocNo,
        public readonly ?string $orderDocDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->organizationId <= 0) {
            $errors['organization_id'] = 'กรุณาระบุหน่วยงานเจ้าของงบ';
        }

        if ($this->baseSalary < 0) {
            $errors['base_salary'] = 'เงินเดือนต้องไม่ติดลบ';
        }

        if (!in_array($this->salaryBasis, ['actual', 'estimated'], true)) {
            $errors['salary_basis'] = 'สถานะเงินเดือนไม่ถูกต้อง';
        }

        if ($this->salaryPreRaise !== null && $this->salaryPreRaise < 0) {
            $errors['salary_pre_raise'] = 'เงินเดือนก่อนเลื่อนต้องไม่ติดลบ';
        }

        if (!in_array($this->occupancy, ['occupied', 'vacant_funded', 'vacant_unfunded'], true)) {
            $errors['occupancy'] = 'สถานะการครองไม่ถูกต้อง';
        }

        if (!in_array($this->lifecycle, ['active', 'abolished'], true)) {
            $errors['lifecycle'] = 'สถานะอัตราไม่ถูกต้อง';
        }

        if ($this->monthsCounted < 1 || $this->monthsCounted > 12) {
            $errors['months_counted'] = 'จำนวนเดือนที่นับต้องอยู่ระหว่าง 1-12';
        }

        if (!in_array($this->approvalStatus, ['approved', 'requested'], true)) {
            $errors['approval_status'] = 'สถานะการอนุมัติไม่ถูกต้อง';
        }

        if (!$this->isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มมีผลไม่ถูกต้อง';
        }

        if ($this->effectiveTo !== null && !$this->isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดผลไม่ถูกต้อง';
        } elseif ($this->effectiveTo !== null && $this->effectiveTo < $this->effectiveFrom) {
            $errors['effective_to'] = 'วันสิ้นสุดผลต้องไม่ก่อนวันเริ่มมีผล';
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
            organizationId: (int) ($raw['organization_id'] ?? 0),
            posNo: self::nullableString($raw['pos_no'] ?? null),
            levelCode: self::nullableString($raw['level_code'] ?? null),
            lineCode: self::nullableString($raw['line_code'] ?? null),
            baseSalary: (float) ($raw['base_salary'] ?? 0),
            salaryBasis: (string) ($raw['salary_basis'] ?? 'estimated'),
            salaryPreRaise: isset($raw['salary_pre_raise']) ? (float) $raw['salary_pre_raise'] : null,
            occupancy: (string) ($raw['occupancy'] ?? 'occupied'),
            lifecycle: (string) ($raw['lifecycle'] ?? 'active'),
            monthsCounted: (int) ($raw['months_counted'] ?? 12),
            approvalStatus: (string) ($raw['approval_status'] ?? 'approved'),
            effectiveFrom: trim((string) ($raw['effective_from'] ?? '')),
            effectiveTo: self::nullableString($raw['effective_to'] ?? null),
            orderDocNo: self::nullableString($raw['order_doc_no'] ?? null),
            orderDocDate: self::nullableString($raw['order_doc_date'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $trimmed = trim((string) $value);
        return $trimmed === '' ? null : $trimmed;
    }

    private function isValidDate(string $date): bool
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return false;
        }
        $parts = explode('-', $date);
        return checkdate((int) $parts[1], (int) $parts[2], (int) $parts[0]);
    }
}
