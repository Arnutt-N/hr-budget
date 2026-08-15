<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class CreatePositionAllowanceDto
{
    use RequestHelpers;

    public function __construct(
        public readonly int $positionId,
        public readonly int $allowanceTypeId,
        public readonly string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->positionId <= 0) {
            $errors['position_id'] = 'กรุณาระบุอัตรากำลัง';
        }
        if ($this->allowanceTypeId <= 0) {
            $errors['allowance_type_id'] = 'กรุณาระบุชนิดเงินเพิ่ม';
        }
        if (!self::isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มมีสิทธิ์ไม่ถูกต้อง';
        }
        if ($this->effectiveTo !== null && !self::isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดไม่ถูกต้อง';
        } elseif ($this->effectiveTo !== null && $this->effectiveTo < $this->effectiveFrom) {
            $errors['effective_to'] = 'วันสิ้นสุดต้องไม่ก่อนวันเริ่ม';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        return self::fromArray(self::jsonBody());
    }

    /** @param array<string,mixed> $raw */
    public static function fromArray(array $raw): self
    {
        return new self(
            positionId: (int) ($raw['position_id'] ?? 0),
            allowanceTypeId: (int) ($raw['allowance_type_id'] ?? 0),
            effectiveFrom: trim((string) ($raw['effective_from'] ?? '')),
            effectiveTo: self::nullableString($raw['effective_to'] ?? null),
            docNo: self::nullableString($raw['doc_no'] ?? null),
        );
    }
}
