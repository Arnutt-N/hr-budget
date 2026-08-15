<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdateSalaryScaleDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?float $minAmount,
        public readonly ?float $maxAmount,
        public readonly ?string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->minAmount !== null && $this->minAmount < 0) {
            $errors['min_amount'] = 'อัตราขั้นต่ำต้องไม่ติดลบ';
        }
        if ($this->maxAmount !== null && $this->maxAmount < 0) {
            $errors['max_amount'] = 'อัตราขั้นสูงต้องไม่ติดลบ';
        }
        if ($this->minAmount !== null && $this->maxAmount !== null && $this->maxAmount < $this->minAmount) {
            $errors['max_amount'] = 'อัตราขั้นสูงต้องไม่ต่ำกว่าขั้นต่ำ';
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
            minAmount: array_key_exists('min_amount', $raw) ? (float) $raw['min_amount'] : null,
            maxAmount: array_key_exists('max_amount', $raw) ? (float) $raw['max_amount'] : null,
            effectiveFrom: array_key_exists('effective_from', $raw) ? (string) $raw['effective_from'] : null,
            effectiveTo: array_key_exists('effective_to', $raw) ? (string) $raw['effective_to'] : null,
            docNo: array_key_exists('doc_no', $raw) ? self::nullableString($raw['doc_no']) : null,
        );
    }
}
