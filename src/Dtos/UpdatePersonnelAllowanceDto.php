<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdatePersonnelAllowanceDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?float $amount,
        public readonly ?string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
        public readonly ?string $docDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->amount !== null && $this->amount < 0) {
            $errors['amount'] = 'ยอดต้องไม่ติดลบ';
        }
        if ($this->effectiveFrom !== null && !self::isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มรับไม่ถูกต้อง';
        }
        if ($this->effectiveTo !== null && !self::isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดไม่ถูกต้อง';
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
            amount: array_key_exists('amount', $raw) ? (float) $raw['amount'] : null,
            effectiveFrom: array_key_exists('effective_from', $raw) ? (string) $raw['effective_from'] : null,
            effectiveTo: array_key_exists('effective_to', $raw) ? self::nullableString($raw['effective_to']) : null,
            docNo: array_key_exists('doc_no', $raw) ? self::nullableString($raw['doc_no']) : null,
            docDate: array_key_exists('doc_date', $raw) ? self::nullableString($raw['doc_date']) : null,
        );
    }

    public function toUpdateData(): array
    {
        $map = [
            'amount' => $this->amount,
            'effective_from' => $this->effectiveFrom,
            'effective_to' => $this->effectiveTo,
            'doc_no' => $this->docNo,
            'doc_date' => $this->docDate,
        ];
        return array_filter($map, fn ($value) => $value !== null);
    }
}
