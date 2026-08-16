<?php

declare(strict_types=1);

namespace App\Dtos;

use App\Dtos\Concerns\RequestHelpers;

final class UpdateVacancyRecruitmentDto
{
    use RequestHelpers;

    public function __construct(
        public readonly ?string $type,
        public readonly ?string $docNo,
        public readonly ?string $docDate,
    ) {}

    /** @return array<string,string> */
    public function validate(): array
    {
        $errors = [];

        if ($this->type !== null
            && !in_array($this->type, ['transfer_request', 'eligibility_list', 'ready_to_fill'], true)) {
            $errors['type'] = 'ประเภทหลักฐานไม่ถูกต้อง';
        }
        if ($this->docDate !== null && !self::isValidDate($this->docDate)) {
            $errors['doc_date'] = 'วันที่เอกสารไม่ถูกต้อง';
        }

        return $errors;
    }

    public static function fromRequest(): self
    {
        $raw = self::jsonBody();

        $cleared = self::parseClearedFields($raw, ['doc_no', 'doc_date']);

        return (new self(
            type: array_key_exists('type', $raw) ? (string) $raw['type'] : null,
            docNo: array_key_exists('doc_no', $raw) ? self::nullableString($raw['doc_no']) : null,
            docDate: array_key_exists('doc_date', $raw) ? self::nullableString($raw['doc_date']) : null,
        ))->withCleared($cleared);
    }

    public function toUpdateData(): array
    {
        $map = [
            'type' => $this->type,
            'doc_no' => $this->docNo,
            'doc_date' => $this->docDate,
        ];
        return $this->applyCleared(array_filter($map, fn ($value) => $value !== null));
    }
}
