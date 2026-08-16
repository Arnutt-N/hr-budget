<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdatePersonnelAssignmentDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?int $servingOrganizationId,
        public readonly ?string $effectiveFrom,
        public readonly ?string $effectiveTo,
        public readonly ?string $docNo,
        public readonly ?string $docDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->effectiveFrom !== null && !self::isValidDate($this->effectiveFrom)) {
            $errors['effective_from'] = 'วันเริ่มไปช่วยไม่ถูกต้อง';
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

        $cleared = self::parseClearedFields($raw, ['effective_to', 'doc_no', 'doc_date']);

        return (new self(
            servingOrganizationId: array_key_exists('serving_organization_id', $raw)
                ? (int) $raw['serving_organization_id']
                : null,
            effectiveFrom: array_key_exists('effective_from', $raw) ? (string) $raw['effective_from'] : null,
            effectiveTo: array_key_exists('effective_to', $raw) ? self::nullableString($raw['effective_to']) : null,
            docNo: array_key_exists('doc_no', $raw) ? self::nullableString($raw['doc_no']) : null,
            docDate: array_key_exists('doc_date', $raw) ? self::nullableString($raw['doc_date']) : null,
        ))->withCleared($cleared);
    }

    public function toUpdateData(): array
    {
        $map = [
            'serving_organization_id' => $this->servingOrganizationId,
            'effective_from' => $this->effectiveFrom,
            'effective_to' => $this->effectiveTo,
            'doc_no' => $this->docNo,
            'doc_date' => $this->docDate,
        ];
        return $this->applyCleared(array_filter($map, fn ($value) => $value !== null));
    }
}
