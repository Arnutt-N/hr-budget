<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class CreatePersonnelAllowanceDto
{
    use RequestHelpers;

    public function __construct(
        public readonly string $personId,
        public readonly int $positionId,
        public readonly int $allowanceTypeId,
        public readonly float $amount,
        public readonly string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
        public readonly ?string $docDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->personId === '') {
            $errors['person_id'] = 'กรุณาระบุรหัสบุคคล';
        }
        if ($this->positionId <= 0) {
            $errors['position_id'] = 'กรุณาระบุอัตรากำลัง';
        }
        if ($this->allowanceTypeId <= 0) {
            $errors['allowance_type_id'] = 'กรุณาระบุชนิดเงินเพิ่ม';
        }
        if ($this->amount < 0) {
            $errors['amount'] = 'ยอดต้องไม่ติดลบ';
        }
        if (!self::isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มรับไม่ถูกต้อง';
        }
        if ($this->effectiveTo !== null && !self::isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดไม่ถูกต้อง';
        } elseif ($this->effectiveTo !== null && $this->effectiveTo < $this->effectiveFrom) {
            $errors['effective_to'] = 'วันสิ้นสุดต้องไม่ก่อนวันเริ่ม';
        }
        if ($this->docDate !== null && !self::isValidDate($this->docDate)) {
            $errors['doc_date'] = 'วันที่เอกสารไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        return new self(
            personId: trim((string) ($raw['person_id'] ?? '')),
            positionId: (int) ($raw['position_id'] ?? 0),
            allowanceTypeId: (int) ($raw['allowance_type_id'] ?? 0),
            amount: (float) ($raw['amount'] ?? 0),
            effectiveFrom: trim((string) ($raw['effective_from'] ?? '')),
            effectiveTo: self::nullableString($raw['effective_to'] ?? null),
            docNo: self::nullableString($raw['doc_no'] ?? null),
            docDate: self::nullableString($raw['doc_date'] ?? null),
        );
    }
}
