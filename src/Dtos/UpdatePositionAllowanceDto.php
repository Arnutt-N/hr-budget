<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdatePositionAllowanceDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->effectiveFrom !== null && !self::isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มมีสิทธิ์ไม่ถูกต้อง';
        }
        if ($this->effectiveTo !== null && !self::isValidDate($this->effectiveTo)) {
            $errors['effective_to'] = 'วันสิ้นสุดไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        return new self(
            effectiveFrom: array_key_exists('effective_from', $raw) ? (string) $raw['effective_from'] : null,
            effectiveTo: array_key_exists('effective_to', $raw) ? self::nullableString($raw['effective_to']) : null,
            docNo: array_key_exists('doc_no', $raw) ? self::nullableString($raw['doc_no']) : null,
        );
    }

    public function toUpdateData(): array
    {
        $map = [
            'effective_from' => $this->effectiveFrom,
            'effective_to' => $this->effectiveTo,
            'doc_no' => $this->docNo,
        ];
        return array_filter($map, fn ($value) => $value !== null);
    }
}
