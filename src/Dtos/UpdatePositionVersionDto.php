<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdatePositionVersionDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?int $organizationId,
        public readonly ?string $posNo,
        public readonly ?string $levelCode,
        public readonly ?string $lineCode,
        public readonly ?float $baseSalary,
        public readonly ?string $salaryBasis,
        public readonly ?float $salaryPreRaise,
        public readonly ?string $occupancy,
        public readonly ?string $lifecycle,
        public readonly ?int $monthsCounted,
        public readonly ?string $approvalStatus,
        public readonly ?string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $orderDocNo,
        public readonly ?string $orderDocDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->baseSalary !== null && $this->baseSalary < 0) {
            $errors['base_salary'] = 'เงินเดือนต้องไม่ติดลบ';
        }
        if ($this->salaryBasis !== null && !in_array($this->salaryBasis, ['actual', 'estimated'], true)) {
            $errors['salary_basis'] = 'สถานะเงินเดือนไม่ถูกต้อง';
        }
        if ($this->salaryPreRaise !== null && $this->salaryPreRaise < 0) {
            $errors['salary_pre_raise'] = 'เงินเดือนก่อนเลื่อนต้องไม่ติดลบ';
        }
        if ($this->occupancy !== null
            && !in_array($this->occupancy, ['occupied', 'vacant_funded', 'vacant_unfunded'], true)) {
            $errors['occupancy'] = 'สถานะการครองไม่ถูกต้อง';
        }
        if ($this->lifecycle !== null && !in_array($this->lifecycle, ['active', 'abolished'], true)) {
            $errors['lifecycle'] = 'สถานะอัตราไม่ถูกต้อง';
        }
        if ($this->monthsCounted !== null && ($this->monthsCounted < 1 || $this->monthsCounted > 12)) {
            $errors['months_counted'] = 'จำนวนเดือนที่นับต้องอยู่ระหว่าง 1-12';
        }
        if ($this->approvalStatus !== null
            && !in_array($this->approvalStatus, ['approved', 'requested'], true)) {
            $errors['approval_status'] = 'สถานะการอนุมัติไม่ถูกต้อง';
        }
        if ($this->effectiveFrom !== null && !self::isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มมีผลไม่ถูกต้อง';
        }
        if ($this->effectiveTo !== null && !self::isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดผลไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        return new self(
            organizationId: array_key_exists('organization_id', $raw) ? (int) $raw['organization_id'] : null,
            posNo: array_key_exists('pos_no', $raw) ? self::nullableString($raw['pos_no']) : null,
            levelCode: array_key_exists('level_code', $raw) ? self::nullableString($raw['level_code']) : null,
            lineCode: array_key_exists('line_code', $raw) ? self::nullableString($raw['line_code']) : null,
            baseSalary: array_key_exists('base_salary', $raw) ? (float) $raw['base_salary'] : null,
            salaryBasis: array_key_exists('salary_basis', $raw) ? (string) $raw['salary_basis'] : null,
            salaryPreRaise: array_key_exists('salary_pre_raise', $raw)
                ? ($raw['salary_pre_raise'] === null ? null : (float) $raw['salary_pre_raise'])
                : null,
            occupancy: array_key_exists('occupancy', $raw) ? (string) $raw['occupancy'] : null,
            lifecycle: array_key_exists('lifecycle', $raw) ? (string) $raw['lifecycle'] : null,
            monthsCounted: array_key_exists('months_counted', $raw) ? (int) $raw['months_counted'] : null,
            approvalStatus: array_key_exists('approval_status', $raw) ? (string) $raw['approval_status'] : null,
            effectiveFrom: array_key_exists('effective_from', $raw) ? (string) $raw['effective_from'] : null,
            effectiveTo: array_key_exists('effective_to', $raw) ? (string) $raw['effective_to'] : null,
            orderDocNo: array_key_exists('order_doc_no', $raw) ? self::nullableString($raw['order_doc_no']) : null,
            orderDocDate: array_key_exists('order_doc_date', $raw) ? self::nullableString($raw['order_doc_date']) : null,
        );
    }

    /** แปลงเป็นชุดคอลัมน์ที่ repository อัปเดตได้ (null = ไม่แตะ) */
    public function toUpdateData(): array
    {
        $map = [
            'organization_id' => $this->organizationId,
            'pos_no' => $this->posNo,
            'level_code' => $this->levelCode,
            'line_code' => $this->lineCode,
            'base_salary' => $this->baseSalary,
            'salary_basis' => $this->salaryBasis,
            'salary_pre_raise' => $this->salaryPreRaise,
            'occupancy' => $this->occupancy,
            'lifecycle' => $this->lifecycle,
            'months_counted' => $this->monthsCounted,
            'approval_status' => $this->approvalStatus,
            'effective_from' => $this->effectiveFrom,
            'effective_to' => $this->effectiveTo,
            'order_doc_no' => $this->orderDocNo,
            'order_doc_date' => $this->orderDocDate,
        ];

        return array_filter(
            $map,
            fn ($value) => $value !== null,
        );
    }
}
