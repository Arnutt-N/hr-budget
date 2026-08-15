<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class CreatePositionDto
{
    use RequestHelpers;

    public const CATEGORIES = ['civil_servant', 'government_employee', 'permanent_employee'];

    public function __construct(
        public readonly string $payNo,
        public readonly string $employeeCategory,
        public readonly ?string $createdDocNo,
        // ฟิลด์เวอร์ชันแรก (สร้างอัตราต้องมีสภาพเริ่มต้นเสมอ)
        public readonly int $organizationId,
        public readonly ?string $posNo,
        public readonly ?string $levelCode,
        public readonly ?string $lineCode,
        public readonly float $baseSalary,
        public readonly string $occupancy,
        public readonly int $monthsCounted,
        public readonly string $effectiveFrom,
        public readonly ?string $orderDocNo,
        public readonly ?string $orderDocDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->payNo === '') {
            $errors['pay_no'] = 'กรุณาระบุเลขถือจ่าย';
        }

        if (!in_array($this->employeeCategory, self::CATEGORIES, true)) {
            $errors['employee_category'] = 'ประเภทบุคลากรไม่ถูกต้อง';
        }

        if ($this->organizationId <= 0) {
            $errors['organization_id'] = 'กรุณาระบุหน่วยงานเจ้าของงบ';
        }

        if ($this->baseSalary < 0) {
            $errors['base_salary'] = 'เงินเดือนต้องไม่ติดลบ';
        }

        if (!in_array($this->occupancy, ['occupied', 'vacant_funded', 'vacant_unfunded'], true)) {
            $errors['occupancy'] = 'สถานะการครองไม่ถูกต้อง';
        }

        if ($this->monthsCounted < 1 || $this->monthsCounted > 12) {
            $errors['months_counted'] = 'จำนวนเดือนที่นับต้องอยู่ระหว่าง 1-12';
        }

        if (!$this->isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มมีผลไม่ถูกต้อง';
        }

        if ($this->orderDocDate !== null && !$this->isValidDate($this->orderDocDate)) {
            $errors['order_doc_date'] = 'วันที่คำสั่งไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        return new self(
            payNo: trim((string) ($raw['pay_no'] ?? '')),
            employeeCategory: (string) ($raw['employee_category'] ?? ''),
            createdDocNo: self::nullableString($raw['created_doc_no'] ?? null),
            organizationId: (int) ($raw['organization_id'] ?? 0),
            posNo: self::nullableString($raw['pos_no'] ?? null),
            levelCode: self::nullableString($raw['level_code'] ?? null),
            lineCode: self::nullableString($raw['line_code'] ?? null),
            baseSalary: (float) ($raw['base_salary'] ?? 0),
            occupancy: (string) ($raw['occupancy'] ?? 'occupied'),
            monthsCounted: (int) ($raw['months_counted'] ?? 12),
            effectiveFrom: trim((string) ($raw['effective_from'] ?? '')),
            orderDocNo: self::nullableString($raw['order_doc_no'] ?? null),
            orderDocDate: self::nullableString($raw['order_doc_date'] ?? null),
        );
    }
}
